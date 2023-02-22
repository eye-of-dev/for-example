<?php

namespace app\components\CdekComponent\resources;

use Rakit\Validation\Validator;

class Base
{
    /**
     * Правила для валидаций
     * @var array
     */
    protected array $rules = [];

    /**
     * Ошибки валидации
     * @var array
     */
    protected array $validationErrors = [];

    public function __construct(array $param = [])
    {
        foreach ($param as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }

    /**
     * Валидация правил
     * @return bool
     */
    public function validate(): bool
    {
        $validator = new Validator();
        $validation = $validator->validate(get_object_vars($this), $this->rules);

        if ($validation->fails()) {
            $this->validationErrors[] = $validation->errors()->all();
        }
        return $validation->passes();
    }
}