<?php
/**
 * Template Fields plugin for Craft CMS 3.x
 *
 * Display parsed twig template content in a read-only field
 *
 * @link      https://www.cdfwebsolutions.com
 * @copyright Copyright (c) 2020 CDF Web Solutions
 */

namespace cdfwebsolutions\templatefields;

use cdfwebsolutions\templatefields\fields\TemplateFieldsField as TemplateFieldsField;

use Craft;
use craft\base\Plugin;
use craft\services\Plugins;
use craft\events\PluginEvent;
use craft\services\Fields;
use craft\events\RegisterComponentTypesEvent;

use yii\base\Event;

/**
 * Class TemplateFields
 *
 * @author    CDF Web Solutions
 * @package   TemplateFields
 * @since     1.0.0
 *
 */
class TemplateFields extends Plugin
{
    // Static Properties
    // =========================================================================

    /**
     * @var TemplateFields
     */
    public static $plugin;

    // Public Properties
    // =========================================================================

    /**
     * @var string
     */
    public $schemaVersion = '1.0.0';

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        self::$plugin = $this;

        Event::on(
            Fields::class,
            Fields::EVENT_REGISTER_FIELD_TYPES,
            function (RegisterComponentTypesEvent $event) {
                $event->types[] = TemplateFieldsField::class;
            }
        );

        Event::on(
            Plugins::class,
            Plugins::EVENT_AFTER_INSTALL_PLUGIN,
            function (PluginEvent $event) {
                if ($event->plugin === $this) {
                }
            }
        );

        Craft::info(
            Craft::t(
                'template-fields',
                '{name} plugin loaded',
                ['name' => $this->name]
            ),
            __METHOD__
        );
    }

    // Protected Methods
    // =========================================================================

}
