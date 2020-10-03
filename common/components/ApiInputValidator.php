<?php
namespace common\components;


class ApiInputValidator
{

    public static $validationErrorKey;
    public static $validationErrorMessage;


    /**
     * API Validation method
     * @todo Validate
     */

    public static function validateInputs($required = [], $optional = [], $request)
    {
        foreach($required as $eachRequired)
        {
            $thisKey = (isset($request->$eachRequired)) ? $request->$eachRequired : NULL;

            if(!isset($thisKey) && empty($thisKey))
            {
                self::$validationErrorKey = $eachRequired;
                self::$validationErrorMessage = sprintf('Parameter "%s" is missing', $eachRequired);
                return false;
            }
        }
        return true;
    }
}
