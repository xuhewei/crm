<?php

namespace Oro\Bundle\MagentoBundle\Provider\Analytics;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\AnalyticsBundle\Builder\RFMProviderInterface;
use Oro\Bundle\AnalyticsBundle\Model\RFMAwareInterface;
use Oro\Bundle\ChannelBundle\Entity\Channel;

abstract class AbstractCustomerRFMProvider implements RFMProviderInterface
{
    /**
     * @var DoctrineHelper
     */
    protected $doctrineHelper;

    /**
     * @var string
     */
    protected $className;

    /**
     * @param DoctrineHelper $doctrineHelper
     * @param string $className
     */
    public function __construct(DoctrineHelper $doctrineHelper, $className)
    {
        $this->doctrineHelper = $doctrineHelper;
        $this->className = $className;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(Channel $channel)
    {
        $entityFQCN = $channel->getCustomerIdentity();
        return is_a($entityFQCN, 'Oro\Bundle\AnalyticsBundle\Model\RFMAwareInterface', true)
            && is_a($entityFQCN, 'Oro\Bundle\ChannelBundle\Model\CustomerIdentityInterface', true)
            && is_a($entityFQCN, $this->className, true);
    }

    /**
     * {@inheritdoc}
     */
    public function getValues(Channel $channel, array $ids = [])
    {
        return array_reduce($this->getScalarValues($channel, $ids), function ($result, array $value) {
            $result[$value['id']] = $value['value'];
            return $result;
        }, []);
    }

    /**
     * @param Channel $channel
     * @param array $ids
     * @return array
     */
    abstract protected function getScalarValues(Channel $channel, array $ids = []);
}
