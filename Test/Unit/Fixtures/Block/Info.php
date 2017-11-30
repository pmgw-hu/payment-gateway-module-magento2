<?php
namespace BigFish\Pmgw\Test\Unit\Fixtures\Block;

class Info extends \BigFish\Pmgw\Block\Info
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
