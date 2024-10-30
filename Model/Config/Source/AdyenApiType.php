<?php

/**
 * Copyright © Alice Dean. All rights reserved.
 */

namespace Deano\AdyenWhitelistApi\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class AdyenApiType implements OptionSourceInterface
{
    /**
     * @inheritDoc
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'merchants', 'label' => 'Merchant'],
            ['value' => 'companies', 'label' => 'Company'],
        ];
    }
}
