<?php

namespace OroCRM\Bundle\ChannelBundle\Model\Condition;

use Oro\Bundle\WorkflowBundle\Exception\ConditionException;

use OroCRM\Bundle\ChannelBundle\Entity\Channel;
use OroCRM\Bundle\ChannelBundle\Provider\StateProvider;

use Oro\Component\ConfigExpression\Condition\AbstractCondition;
use Oro\Component\ConfigExpression\ContextAccessorAwareInterface;
use Oro\Component\ConfigExpression\ContextAccessorAwareTrait;

class ChannelEntityAvailability extends AbstractCondition implements ContextAccessorAwareInterface
{
    use ContextAccessorAwareTrait;

    /** @var  Channel */
    protected $channel;

    /** @var  Array */
    protected $entities;

    /** @var  string */
    protected $message;

    /** @var StateProvider */
    protected $stateProvider;

    public function __construct(StateProvider $stateProvider)
    {
        $this->stateProvider   = $stateProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'channel_entity_availiable';
    }

    /**
     * {@inheritDoc}
     */
    public function initialize(array $options)
    {
        if (2 === count($options)) {
            $this->channel  = $options[0];
            $this->entities = $options[1];
        } elseif (1 === count($options)) {
            $this->entities = $options[0];
        } else {
            throw new ConditionException(
                sprintf(
                    'Invalid options count: %d',
                    count($options)
                )
            );
        }

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function isConditionAllowed($context)
    {
        if (null !== $this->channel) {
            /** @var Channel $dataChannel */
            $dataChannel = $this->resolveValue($context, $this->channel, false);
            $entities    = $dataChannel->getEntities();

            $allowed = count(array_intersect($this->entities, $entities)) === count($this->entities);
        } else {
            $allowed = $this->stateProvider->isEntitiesEnabledInSomeChannel($this->entities);
        }

        return $allowed;
    }

    /**
     * {@inheritDoc}
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        return $this->convertToArray([$this->channel, $this->entities]);
    }

    /**
     * {@inheritdoc}
     */
    public function compile($factoryAccessor)
    {
        return $this->convertToPhpCode([$this->channel, $this->entities], $factoryAccessor);
    }
}
