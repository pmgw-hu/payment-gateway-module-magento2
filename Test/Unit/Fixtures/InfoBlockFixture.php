<?php
namespace BigFish\Pmgw\Test\Unit\Fixtures;

use BigFish\Pmgw\Block\Info;

class InfoBlockFixture extends Info
{
    /**
     * @param string $field
     * @return \Magento\Framework\Phrase
     */
    public function getLabel($field)
    {
        return parent::getLabel($field);
    }

}
