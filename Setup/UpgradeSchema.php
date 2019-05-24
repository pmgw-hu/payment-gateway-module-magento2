<?php

namespace Bigfishpaymentgateway\Pmgw\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        if (version_compare($context->getVersion(), '2.1.0', '<')) {
            $this->addCardRegistration($setup);
        }
    }

    /**
     * @param SchemaSetupInterface $setup
     * @return $this
     */
    protected function addCardRegistration(SchemaSetupInterface $setup)
    {
        $setup->startSetup();
        $setup->getConnection()->addColumn(
            $setup->getTable('bigfish_paymentgateway'),
            'card_registration',
            [
                'type' => Table::TYPE_BOOLEAN,
                'default' => 0,
                'comment' => 'Success card registration'
            ]
        );
        $setup->endSetup();

        return $this;
    }
}
