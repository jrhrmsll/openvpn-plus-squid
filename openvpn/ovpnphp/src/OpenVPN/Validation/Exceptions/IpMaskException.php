<?php

namespace OpenVPN\Validation\Exceptions;

use \Respect\Validation\Exceptions\ValidationException;

class IpMaskException extends ValidationException {

    public function chooseTemplate() {
        echo "Error";
    }

}
