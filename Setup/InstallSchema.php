<?php
/**
* BIG FISH Ltd.
* http://www.bigfish.hu
*
* @title      Magento -> Custom Payment Module for BIG FISH Payment Gateway
* @category   BigFish
* @package    BigFish_Pmgw
* @author     Polyak Sandor / BIG FISH Ltd. -> sandor [dot] polyak [at] bigfish [dot] hu
* @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
* @copyright  Copyright (c) 2017, BIG FISH Ltd.
*/

namespace BigFish\Pmgw\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Class InstallSchema
 *
 * @package BigFish\Pmgw\Setup
 */
class InstallSchema implements InstallSchemaInterface
{

    /**
     * @var \Magento\Framework\App\Config\Storage\WriterInterface
     */
    private $configWriter;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * InstallSchema constructor.
     *
     * @param \Magento\Framework\App\Config\Storage\WriterInterface $configWriter
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(WriterInterface $configWriter, ScopeConfigInterface $scopeConfig)
    {
        $this->configWriter = $configWriter;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param \Magento\Framework\Setup\SchemaSetupInterface $setup
     * @param \Magento\Framework\Setup\ModuleContextInterface $context
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        $tableName = $installer->getTable('bigfish_paymentgateway');

        if ($installer->getConnection()->isTableExists($tableName) != true) {

            $table = $installer->getConnection()
                ->newTable($tableName)
                ->addColumn(
                    'paymentgateway_id',
                    Table::TYPE_INTEGER,
                    11,
                    [
                        'identity' => true,
                        'unsigned' => true,
                        'nullable' => false,
                        'primary' => true
                    ],
                    'PaymentGateway ID'
                )
                ->addColumn(
                    'order_id',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => false, 'default' => ''],
                    'Order ID'
                )
                ->addColumn(
                    'transaction_id',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => false, 'default' => ''],
                    'Transaction ID'
                )
                ->addColumn(
                    'created_time',
                    Table::TYPE_DATETIME,
                    null,
                    ['nullable' => false],
                    'Created Time'
                )
                ->addColumn(
                    'status',
                    Table::TYPE_SMALLINT,
                    6,
                    ['nullable' => false, 'default' => '0'],
                    'Status'
                )
                ->setComment('BigFish PaymentGateway')
                ->setOption('type', 'InnoDB')
                ->setOption('charset', 'utf8');
            $installer->getConnection()->createTable($table);
        }

        $tableName = $installer->getTable('bigfish_paymentgateway_log');

        if ($installer->getConnection()->isTableExists($tableName) != true) {

            $table = $installer->getConnection()
                ->newTable($tableName)
                ->addColumn(
                    'log_id',
                    Table::TYPE_INTEGER,
                    11,
                    [
                        'identity' => true,
                        'unsigned' => true,
                        'nullable' => false,
                        'primary' => true
                    ],
                    'Log ID'
                )
                ->addColumn(
                    'paymentgateway_id',
                    Table::TYPE_INTEGER,
                    11,
                    [
                        'identity' => false,
                        'unsigned' => true,
                        'nullable' => false
                    ],
                    'PaymentGateway ID'
                )
                ->addColumn(
                    'created_time',
                    Table::TYPE_DATETIME,
                    null,
                    ['nullable' => false],
                    'Created Time'
                )
                ->addColumn(
                    'status',
                    Table::TYPE_SMALLINT,
                    6,
                    ['nullable' => false, 'default' => '0'],
                    'Status'
                )
                ->addColumn(
                    'debug',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Debug'
                )
                ->setComment('BigFish PaymentGateway Log')
                ->setOption('type', 'InnoDB')
                ->setOption('charset', 'utf8');
            $installer->getConnection()->createTable($table);
        }

        $deprecatedPaymentMethods = array(
            'mpp',
            'mcm',
            'payu',
            'payuwire',
            'payucash',
            'payumobile',
            'barion',
        );

        if (!empty($deprecatedPaymentMethods)) {
            foreach ($deprecatedPaymentMethods as $paymentMethod) {
                $configPath = 'payment/bigfish_pmgw_' . strtolower($paymentMethod) . '/active';
                if ($this->scopeConfig->getValue($configPath) !== null) {
                    $this->configWriter->save($configPath, 0);
                }
            }
        }

        $installer->endSetup();
    }
}
