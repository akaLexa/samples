<?php
/**
 * MuWebCloneEngine
 * Version: 1.6
 * User: epmak
 * 30.03.2018
 *
 **/

namespace mwce\Validator;

use mwce\Validator\Errors\EmptyFieldError;
use mwce\Validator\Errors\ValidatorErrors;
use mwce\Validator\Errors\WrongSchemeError;

class Validador
{
    /**
     * @var array
     */
    protected $data = array();
    /**
     * @var ValidatorErrors
     */
    protected $errors;

    /**
     * Validador constructor.
     * @param mixed $data
     * @param array $scheme
     */
    public function __construct(array $data, array $scheme)
    {
        $this->errors = new ValidatorErrors();
        if (!empty($scheme)) {

            foreach ($scheme as $name => $_scheme) {

                if (isset($data[$name]) || isset($_scheme['default'])) {

                    if(!isset($data[$name]) || $data[$name] === ''){

                        if(isset($_scheme['default']) && strtolower(trim($_scheme['default']))!=='null'){
                            $this->data[$name] = $_scheme['default'];
                            continue;
                        }

                        $data[$name] = null;
                    }

                    if (empty($_scheme['type'])) {
                        $this->errors->Add(new WrongSchemeError($name, 'type'));
                        continue;
                    }

                    $tmp = self::controlValue($data[$name] ?? null, $_scheme, $name);

                    if (!empty($tmp['error'])) {
                        $this->errors->Add($tmp['error']);
                    }

                    if (isset($tmp['value'])) {
                        $this->data[$name] = $tmp['value'];
                    }

                    unset($data[$name]);
                }
                else {

                    $this->errors->Add(new EmptyFieldError(null, $name));
                }
            }

            /*if (!empty($data)) {
                $this->data += $data;
            }*/
        }
        else{
            $this->errors->Add(new EmptyFieldError(null, 'all Fields'));
        }
    }

    public function getFiltered(): array
    {

        if (!empty($this->data)) {
            $return = [];

            foreach ($this->data as $datum => $datVal) {
                $return[$datum] = $datVal;
            }

            return $return;
        }
        return [];
    }

    public function getErrors(): ValidatorErrors
    {
        return $this->errors;
    }

    /**
     * @param $value
     * @param array $options
     * @param null $legend
     * @return array
     */
    public static function controlValue($value, array $options, $legend = null): array
    {
        $return = [];

        if (null === $value && isset($options['default'])) {
            return [ 'value' => \strtolower(\trim($options['default'])) === 'null' ? null : $options['default'] ];
        }

        try {

            $value = new $options['type']($value, $legend);

            $return['value'] = $value->getValue();

            if (!empty($options['length'])) {
                $return['value'] = substr($return['value'], 0, $options['length']);
            }
        }
        catch (\Exception $ve) {
            $return['error'] = $ve;
        }

        return $return;
    }

}