<?php
/**
 * BIG FISH Ltd.
 * http://www.bigfish.hu
 *
 * @title      Magento -> Custom Payment Module for BIG FISH Payment Gateway
 * @category   BigFish
 * @package    BigFish_Pmgw
 * @author     BIG FISH Ltd., paymentgateway [at] bigfish [dot] hu
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @copyright  Copyright (c) 2017, BIG FISH Ltd.
 */
namespace BigFish\Pmgw\Model\Resource\Log;

use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Psr\Log\LoggerInterface;

/**
 * Class LogCollection
 *
 * @package BigFish\Pmgw\Model\Resource\Log
 */
class LogCollection extends AbstractCollection
{

    /**
     * @var AbstractDb|NULL
     */
    protected $_resourceModel;

    /**
     * @var EntityFactoryInterface
     */
    private $entityFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var FetchStrategyInterface
     */
    private $fetchStrategy;

    /**
     * @var ManagerInterface
     */
    private $eventManager;

    /**
     * @var AdapterInterface|NULL
     */
    private $connection;

    /**
     * LogCollection constructor.
     *
     * @param EntityFactoryInterface $entityFactory
     * @param LoggerInterface $logger
     * @param FetchStrategyInterface $fetchStrategy
     * @param ManagerInterface $eventManager
     * @param AdapterInterface|NULL $connection
     * @param AbstractDb|NULL $resource
     */
    public function __construct(
        EntityFactoryInterface $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        AdapterInterface $connection = null,
        AbstractDb $resource = null
    ) {
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $connection,
            $resource
        );

        $this->_resourceModel = $resource;
        $this->entityFactory = $entityFactory;
        $this->logger = $logger;
        $this->fetchStrategy = $fetchStrategy;
        $this->eventManager = $eventManager;
        $this->connection = $connection;
    }

    public function _construct()
    {
        $this->_init('BigFish\Pmgw\Model\LogAbstract', 'BigFish\Pmgw\Model\Resource\Log');
    }
}