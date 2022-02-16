<?php
/**
 * Template Fields plugin for Craft CMS 3.x
 *
 * Display parsed twig template content in a read-only field
 *
 * @link      https://www.cdfwebsolutions.com
 * @copyright Copyright (c) 2020 CDF Web Solutions
 */

namespace cdfwebsolutions\templatefields\fields;

use cdfwebsolutions\templatefields\TemplateFields;
//use cdfwebsolutions\templatefields\assetbundles\templatefieldsfield\TemplateFieldsFieldAsset;

use Craft;
use craft\base\ElementInterface;
use craft\base\Field;
use craft\helpers\Db;
use yii\db\Schema;
use craft\helpers\Json;

/**
 * @author    CDF Web Solutions
 * @package   TemplateFields
 * @since     1.0.0
 */
class TemplateFieldsField extends Field
{
    // Public Properties
    // =========================================================================

    /**
     * @var string
     */
    public $templateIncludePath = '';
    public $twigToRender = '';

    // Static Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return Craft::t('template-fields', 'Template Fields');
    }

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = parent::rules();
        $rules = array_merge($rules, [
            ['templateIncludePath', 'string'],
            ['twigToRender', 'string']
        ]);
        return $rules;
    }

    /**
     * @inheritdoc
     */
    public function getContentColumnType(): string
    {
        return Schema::TYPE_STRING;
    }

    /**
     * @inheritdoc
     */
    public function normalizeValue($value, ElementInterface $element = null)
    {
        return $value;
    }

    /**
     * @inheritdoc
     */
    public function serializeValue($value, ElementInterface $element = null)
    {
        return parent::serializeValue($value, $element);
    }

    /**
     * @inheritdoc
     */
    public function getSettingsHtml()
    {
        // Render the settings template
        return Craft::$app->getView()->renderTemplate(
            'template-fields/_components/fields/TemplateFields_settings',
            [
                'field' => $this,
            ]
        );
    }

    /**
     * Returns the field’s input HTML. This is html content rendered from Twig input in the "Twig Code to Render" settings field or the "Template to include" settings field. Content is read-only and not stored in the database.
     * 
     * Passes field and element objects to Twig template code, as well as 'type' variable ('user' or 'entry').
     * 
     * Included template takes precedence over Twig code entered in settings field.
     */
    public function getInputHtml($value, ElementInterface $element = null): string
    {
        // WHAT KIND OF ENTRY IS THIS (e.g., USER OR ENTRY)? - return just class name w/o namespace
        $elClass = get_class($element);
        if ($pos = strrpos($elClass, '\\')){
            $elClass = substr($elClass, $pos + 1);
        }

        $html = "";

        // INCLUDED TEMPLATE FROM SETTINGS
        if($this->templateIncludePath){

            $view = Craft::$app->getView();
            $templateMode = $view->getTemplateMode();
            $view->setTemplateMode($view::TEMPLATE_MODE_SITE);

            $templateExists = $view->doesTemplateExist($this->templateIncludePath);

            if($templateExists){
            
                try {
                    $html = Craft::$app->getView()->renderTemplate($this->templateIncludePath,[
                        'field' => $this,
                        'element' => $element,
                        'type' => $elClass
                    ]);
                }
                catch (\Exception $e) {
                    $html = '<div class="error"><p>There was an error in the included template.</p></div>';
                }
            
            } else {

                $html = '<div class="error"><p>Error: cannot find included template.</p></div>';

            }

            $view->setTemplateMode($templateMode);

        } elseif($this->twigToRender) { // IF NO INCLUDE, RENDER TWIG IN TEXTAREA FIELD FROM SETTINGS

            
            try {
                $html = Craft::$app->getView()->renderString(
                    $this->twigToRender,
                    [
                        'field' => $this,
                        'element' => $element,
                        'type' => $elClass
                    ]
                );
            }
            catch (\Exception $e) {
                $html = '<div class="error"><p>There was an error in the Twig code for this field.</p></div>';
            }
            
        }

        return $html;
    }
}
