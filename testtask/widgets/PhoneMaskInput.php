<?php

namespace app\widgets;

use yii\helpers\Html;
use yii\helpers\Json;
use yii\web\View;
use yii\widgets\InputWidget;

/**
 * Однострочная маска российского телефона: +7 (___) ___-__-__.
 *
 * Понимает ввод и вставку в форматах +7, 8, с пробелами и скобками.
 */
class PhoneMaskInput extends InputWidget
{
    /**
     * {@inheritdoc}
     */
    public function run()
    {
        Html::addCssClass($this->options, 'form-control phone-mask-input');
        $this->options['type'] = 'tel';
        $this->options['inputmode'] = 'tel';
        $this->options['autocomplete'] = 'tel';
        $this->options['placeholder'] = '+7 (___) ___-__-__';
        $this->options['maxlength'] = 18;

        $inputId = $this->options['id'] ?? Html::getInputId($this->model, $this->attribute);
        $this->options['id'] = $inputId;

        $this->registerClientScript($inputId);

        if ($this->hasModel()) {
            return Html::activeTextInput($this->model, $this->attribute, $this->options);
        }

        return Html::textInput($this->name, $this->value, $this->options);
    }

    /**
     * @param string $inputId
     */
    protected function registerClientScript($inputId)
    {
        $selector = Json::encode('#' . $inputId);
        $js = <<<JS
(function () {
    var input = document.querySelector($selector);
    if (!input || input.dataset.phoneMaskReady) {
        return;
    }
    input.dataset.phoneMaskReady = '1';

    function formatPhone(raw) {
        var digits = String(raw || '').replace(/\\D+/g, '');
        if (digits.charAt(0) === '8' || digits.charAt(0) === '7') {
            digits = digits.substring(1);
        }
        digits = digits.substring(0, 10);
        if (!digits) {
            return '';
        }
        var result = '+7 (';
        result += digits.substring(0, 3);
        if (digits.length >= 3) {
            result += ') ' + digits.substring(3, 6);
        }
        if (digits.length >= 6) {
            result += '-' + digits.substring(6, 8);
        }
        if (digits.length >= 8) {
            result += '-' + digits.substring(8, 10);
        }
        return result;
    }

    function applyMask() {
        input.value = formatPhone(input.value);
    }

    input.addEventListener('input', applyMask);
    input.addEventListener('paste', function () {
        setTimeout(applyMask, 0);
    });
    input.addEventListener('blur', applyMask);
    applyMask();
})();
JS;
        $this->view->registerJs($js, View::POS_READY, 'phone-mask-' . $inputId);
    }
}
