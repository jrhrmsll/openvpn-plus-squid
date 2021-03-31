<?php

namespace OpenVPN\Validation\Rules;

use Respect\Validation\Rules\AbstractRule;

/**
 * Description of IpMask
 *
 * @author jrhrmsll
 */
class IpMask extends AbstractRule {

    public function validate($mask) {
        return false;
    }

}
