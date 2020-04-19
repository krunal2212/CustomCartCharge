<?php
namespace Krunal\PercentShipping\Model\Config\Source\Customer;

use Magento\Customer\Model\ResourceModel\Group\Collection as CustomerGroupCollection;

class Groups implements \Magento\Framework\Option\ArrayInterface
{
    protected $_options;

    protected $_groupCollection;

    public function __construct(
        CustomerGroupCollection $groupCollection
    ) {
        $this->_groupCollection = $groupCollection;
    }

    public function toOptionArray()
    {
        if (!$this->_options) {
            $this->_options = $this->_groupCollection->loadData()->toOptionArray();
        }
        return $this->_options;
    }
}