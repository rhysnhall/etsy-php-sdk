<?php

namespace Etsy\Exception;

/**
 * Thrown by Shop::all() when no keyword is given. It extends SdkException, so
 * catching SdkException covers it, like the SDK's other "invalid call" errors.
 *
 * @deprecated Catch SdkException instead. This class will be removed in 2.0.
 */
class ApiException extends SdkException {

}
