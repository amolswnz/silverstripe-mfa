<?php

namespace SilverStripe\MFA\State;

use JsonSerializable;

/**
 * Used to provide details about a registered {@link \SilverStripe\MFA\Method\MethodInterface} instance, for example
 * when being used in the multi factor application schema.
 */
interface RegisteredMethodDetailsInterface extends JsonSerializable
{
    /**
     * @return string
     */
    public function getURLSegment();

    /**
     * @return string
     */
    public function getLeadInLabel();

    /**
     * @return string
     */
    public function getComponent();
}
