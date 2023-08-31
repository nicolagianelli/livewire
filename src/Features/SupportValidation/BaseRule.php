<?php

namespace Livewire\Features\SupportValidation;

use Attribute;
use Livewire\Features\SupportAttributes\Attribute as LivewireAttribute;

use Livewire\Form;
use function Livewire\wrap;

#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_ALL)]
class BaseRule extends LivewireAttribute
{
    // @todo: support custom messages...
    function __construct(
        public $rule,
        protected $attribute = null,
        protected $as = null,
        protected $message = null,
        protected $onUpdate = true,
        protected bool $translate = true
    ) {}

    function boot()
    {
        $formPropertyName = null;
        if (str_contains(str($this->getName()), '.')) {
            $propertyName = explode('.', str($this->getName()))[0];
            if(isset($this->component->$propertyName) && is_subclass_of($this->component->$propertyName, Form::class)){
                $formPropertyName = $propertyName;
            }
        }
        $rules = [];

        // Support setting rules by key-value for this and other properties:
        // For example, #[Rule(['foo' => 'required', 'foo.*' => 'required'])]
        if (is_array($this->rule) && count($this->rule) > 0 && ! is_numeric(array_keys($this->rule)[0])) {
            if ($formPropertyName) {
                foreach ($this->rule as $field => $rule) {
                    if (!str_starts_with($field, "{$formPropertyName}.")) $field = "{$formPropertyName}.{$field}";
                    $rules[$field] = Form::getFixedRule($formPropertyName, $rule);
                }
            } else {
                $rules = $this->rule;
            }
        } else {
            if($formPropertyName) {
                if (is_array($this->rule)) {
                    $rules[$this->getName()] = [];
                    foreach ($this->rule as $rule) {
                        $rules[$this->getName()][] = Form::getFixedRule($formPropertyName, $rule);
                    }
                } else {
                    $rules[$this->getName()] = Form::getFixedRule($formPropertyName, $this->rule);
                }
            } else {
                $rules[$this->getName()] = $this->rule;
            }
        }

        if ($this->attribute) {
            if (is_array($this->attribute)) {
                $this->component->addValidationAttributesFromOutside($this->attribute);
            } else {
                $this->component->addValidationAttributesFromOutside([$this->getName() => $this->attribute]);
            }
        }

        if ($this->as) {
            if (is_array($this->as)) {
                $this->component->addValidationAttributesFromOutside($this->translate ? trans($this->as) : $this->as);
            } else {
                $this->component->addValidationAttributesFromOutside([$this->getName() => $this->translate ? trans($this->as) : $this->as]);
            }
        }

        if ($this->message) {
            if (is_array($this->message)) {
                $this->component->addMessagesFromOutside($this->translate ? trans($this->message) : $this->message);
            } else {
                $this->component->addMessagesFromOutside([$this->getName() => $this->translate ? trans($this->message) : $this->message]);
            }
        }

        $this->component->addRulesFromOutside($rules);
    }

    function update($fullPath, $newValue)
    {
        if ($this->onUpdate === false) return;

        return function () {
            wrap($this->component)->validateOnly($this->getName());
        };
    }
}
