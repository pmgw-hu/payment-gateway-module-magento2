<?php
/**
 * BIG FISH Ltd.
 * http://www.bigfish.hu
 *
 * @title      BIG FISH Payment Gateway module for Magento 2
 * @category   BigFish
 * @package    Bigfishpaymentgateway_Pmgw
 * @author     BIG FISH Ltd., paymentgateway [at] bigfish [dot] hu
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @copyright  Copyright (c) 2017, BIG FISH Ltd.
 */
namespace Bigfishpaymentgateway\Pmgw\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class InstallSchema implements InstallSchemaInterface
{
    const TABLE_NAME_TRANSACTION = 'bigfish_paymentgateway';
    const TABLE_NAME_TRANSACTION_LOG = 'bigfish_paymentgateway_log';

    /**
     * @var array
     */
    protected $deprecatedPaymentMethods = [
        'abaqoos',
        'barion',
        'mcm',
        'mpp',
        'otp2',
        'otpay',
        'otpmultipont',
        'otpsimplewire',
        'payu',
        'payucash',
        'payumobile',
        'payuwire',
        'sms',
    ];

    /**
     * @var WriterInterface
     */
    private $configWriter;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param WriterInterface $configWriter
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(WriterInterface $configWriter, ScopeConfigInterface $scopeConfig)
    {
        $this->configWriter = $configWriter;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (!$this->isTableExists($setup, self::TABLE_NAME_TRANSACTION)) {
            $this->createTransactionTable($setup, self::TABLE_NAME_TRANSACTION);
        }

        if (!$this->isTableExists($setup, self::TABLE_NAME_TRANSACTION_LOG)) {
            $this->createTransactionLogTable($setup, self::TABLE_NAME_TRANSACTION_LOG);
        }

        $this->inactivateDeprecatedPaymentMethods();

        $setup->endSetup();
    }

    /**
     * @param SchemaSetupInterface $setup
     * @param string $tableName
     * @return bool
     */
    protected function isTableExists(SchemaSetupInterface $setup, $tableName)
    {
        return $setup->getConnection()->isTableExists(
            $setup->getTable($tableName)
        );
    }

    /**
     * @param SchemaSetupInterface $setup
     * @param string $tableName
     */
    protected function createTransactionTable(SchemaSetupInterface $setup, $tableName)
    {
        $table = $setup->getConnection()
            ->newTable($tableName)
            ->addColumn(
                'paymentgateway_id',
                Table::TYPE_INTEGER,
                10,
                [
                    'primary' => true,
                    'identity' => true,
                    'unsigned' => true,
                    'nullable' => false,
                ],
                'PaymentGateway ID'
            )
            ->addColumn(
                'order_id',
                Table::TYPE_TEXT,
                32,
                [
                    'nullable' => false,
                ],
                'Order ID'
            )
            ->addColumn(
                'transaction_id',
                Table::TYPE_TEXT,
                32,
                [
                    'nullable' => false,
                ],
                'Transaction ID'
            )
            ->addColumn(
                'created_time',
                Table::TYPE_DATETIME,
                null,
                [
                    'nullable' => false,
                ],
                'Created Time'
            )
            ->addColumn(
                'status',
                Table::TYPE_SMALLINT,
                5,
                [
                    'nullable' => false,
                ],
                'Status'
            )
            ->addIndex(
                $setup->getIdxName(
                    $tableName,
                    ['transaction_id'],
                    AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                ['transaction_id'],
                ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
            )
            ->setComment('BigFish PaymentGateway Transactions')
            ->setOption('type', 'InnoDB')
            ->setOption('charset', 'utf8');

        $setup->getConnection()->createTable($table);
    }

    /**
     * @param SchemaSetupInterface $setup
     * @param string $tableName
     */
    protected function createTransactionLogTable(SchemaSetupInterface $setup, $tableName)
    {
        $table = $setup->getConnection()
            ->newTable($tableName)
            ->addColumn(
                'log_id',
                Table::TYPE_INTEGER,
                10,
                [
                    'primary' => true,
                    'identity' => true,
                    'unsigned' => true,
                    'nullable' => false,
                ],
                'Log ID'
            )
            ->addColumn(
                'paymentgateway_id',
                Table::TYPE_INTEGER,
                10,
                [
                    'identity' => false,
                    'unsigned' => true,
                    'nullable' => false,
                ],
                'PaymentGateway ID'
            )
            ->addColumn(
                'created_time',
                Table::TYPE_DATETIME,
                null,
                [
                    'nullable' => false,
                ],
                'Created Time'
            )
            ->addColumn(
                'status',
                Table::TYPE_SMALLINT,
                6,
                [
                    'nullable' => false,
                ],
                'Status'
            )
            ->addColumn(
                'debug',
                Table::TYPE_TEXT,
                null,
                [
                    'nullable' => false,
                ],
                'Debug'
            )
            ->setComment('BigFish PaymentGateway Transaction Log')
            ->setOption('type', 'InnoDB')
            ->setOption('charset', 'utf8');

        $setup->getConnection()->createTable($table);
    }

    protected function inactivateDeprecatedPaymentMethods()
    {
        if (empty($this->deprecatedPaymentMethods)) {
            return;
        }

        foreach ($this->deprecatedPaymentMethods as $paymentMethod) {
            $configPath = 'payment/bigfishpaymentgateway_pmgw_' . strtolower($paymentMethod) . '/active';

            if ($this->scopeConfig->getValue($configPath) !== null) {
                $this->configWriter->save($configPath, 0);
            }
        }
    }

}
