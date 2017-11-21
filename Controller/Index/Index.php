<?php
namespace BigFish\Pmgw\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use BigFish\Pmgw\Model\TransactionFactory;
use BigFish\Pmgw\Model\LogFactory;

class Index extends Action
{
    /**
     * @var TransactionFactory
     */
    protected $transactionFactory;

    /**
     * @var LogFactory
     */
    protected $logFactory;

    /**
     * @param Context $context
     * @param TransactionFactory $transactionFactory
     */
    public function __construct(
        Context $context,
        TransactionFactory $transactionFactory,
        LogFactory $logFactory
    ) {
        parent::__construct($context);
        $this->transactionFactory = $transactionFactory;
        $this->logFactory = $logFactory;
    }

    public function execute()
    {
        $transactionModel = $this->transactionFactory->create();
        $transactionCollection = $transactionModel->getCollection();

        var_dump($transactionCollection->getData());

        $ogModel = $this->logFactory->create();
        $logCollection = $ogModel->getCollection();

        var_dump($logCollection->getData());

        exit();
    }
}
