<?php
/**
 * Created by PhpStorm.
 * User: Victor Albulescu
 * Date: 06/06/2019
 * Time: 15:25
 */

namespace Ceres\Wizard\ShopWizard\Steps\Builder;

use Ceres\Wizard\ShopWizard\Helpers\LanguagesHelper;
use Ceres\Wizard\ShopWizard\Services\DefaultSettingsService;
use Plenty\Modules\Account\Contact\Contracts\ContactClassRepositoryContract;
use Plenty\Modules\Order\Shipping\Countries\Contracts\CountryRepositoryContract;
use Plenty\Modules\Payment\Contracts\PaymentRepositoryContract;
use Plenty\Modules\Payment\Method\Contracts\PaymentMethodRepositoryContract;
use Plenty\Modules\Accounting\Contracts\AccountingLocationRepositoryContract;

class DefaultSettingsStep extends Step
{


    /**
     * @var PaymentRepositoryContract
     */
    private $paymentRepository;

    /**
     * @var CountryRepositoryContract
     */
    private $countryRepository;

    /**
     * @var ContactClassRepositoryContract
     */
    private $classRepository;

    /**
     * @var AccountingLocationRepositoryContract
     */
    private $locationRepository;


    /**
     * DefaultSettingsStep constructor.
     *
     * @param PaymentMethodRepositoryContract $paymentRepository
     * @param CountryRepositoryContract $countryRepository
     * @param ContactClassRepositoryContract $classRepository
     * @param AccountingLocationRepositoryContract $accountingLocationRepositoryContract
     */
    public function __construct(
        PaymentMethodRepositoryContract $paymentRepository,
        CountryRepositoryContract $countryRepository,
        ContactClassRepositoryContract $classRepository,
        AccountingLocationRepositoryContract $accountingLocationRepositoryContract
    ){
        $this->paymentRepository = $paymentRepository;
        $this->countryRepository = $countryRepository;
        $this->classRepository = $classRepository;
        $this->locationRepository = $accountingLocationRepositoryContract;
    }


    public function generateStep()
    {
        $wizardService = pluginApp(DefaultSettingsService::class);

        $shippingMethods = $wizardService->getShippingMethods();
        $shippingMethodsList = $this->buildListBoxData($shippingMethods);

        $languages = $wizardService->getLanguages();
        $languagesList = $this->buildListBoxData($languages);
        $conditionDisplayGlobals = "client !== 'preview'";

        $shippingProfiles = $wizardService->getShippingProfiles();
        $shippingProfilesList = $this->buildListBoxData($shippingProfiles);

        $paymentMethods = $this->paymentRepository->allPluginPaymentMethods();
        $paymentMethodsList = $this->buildListBoxData($paymentMethods, "name", "id");

        $deliveryCountries = $this->countryRepository->getActiveCountriesList();


        $b2bClasses  = $this->classRepository->allContactClasses();
        $b2bClassesList = $this->buildListBoxData($b2bClasses);

        $locations = $this->locationRepository->getAll();
        $locationsList = $this->buildListBoxData($locations, "name", "id");
        return [
            "title" => "Wizard.defaultSettings",
            "description" => "Wizard.defaultSettingsDescription",
            "sections" => [
                $this->generateSection("defaultLanguage", $languagesList, $conditionDisplayGlobals),
                $this->generateSection("defaultShippingMethod", $shippingMethodsList, $conditionDisplayGlobals),
                $this->generateSection("defaultShippingProfile", $shippingProfilesList, $conditionDisplayGlobals),
                $this->generateSection("defaultPaymentMethod", $paymentMethodsList, $conditionDisplayGlobals),
                $this->generateCountryDeliverySection("defaultDeliveryCountry", $deliveryCountries, $conditionDisplayGlobals),
                $this->generateSection("defaultB2B",$b2bClassesList),
                $this->generateSection("defaultLocation",$locationsList, $conditionDisplayGlobals)
            ]
        ];
    }

    /**
     * @param $name
     *
     * @return array
     */
    private function generateSection($name, $listBoxValues, $condition = true): array
    {
        return [
            "title" => "Wizard." . $name,
            "description" => "Wizard." . $name . "Description",
            "condition" => $condition,
            "form" => [
                "defSettings_" . $name => [
                    "type" => "select",
                    "options" => [
                        "name" => "",
                        "listBoxValues" => $listBoxValues
                    ]
                ]
            ]
        ];
    }

    /**
     * @param $name
     * @param $collection
     * @param bool $condition
     *
     * @return array
     */
    private function generateCountryDeliverySection($name, $collection, $condition = true): array
    {
        return [
            "title" => "Wizard." . $name,
            "description" => "Wizard." . $name . "Description",
            "condition" => $condition,
            "form" => $this->generateCountriesList($collection)
        ];
    }

    /**
     * @param $countriesCollection
     *
     * @return array
     */
    private function generateCountriesList($countriesCollection): array
    {
        $list = [];

        if (count($countriesCollection)) {
            foreach ($countriesCollection as $country) {
                $countryData = $country->toArray();
                $langs = LanguagesHelper::getLanguages();
                $languages = LanguagesHelper::translateLanguages($langs);
                $key = 'defSettings_deliveryCountry_' . $countryData['lang'];
                $list[$key] = [
                    "type" => "select",
                    "options" => [
                        "name" => $languages[$countryData['lang']],
                        'required' => true,
                        "listBoxValues" => [
                            [
                                "value" => $countryData['id'],
                                "caption" => $countryData['name']
                            ]
                        ]
                    ]
                ];
            }
        }

        return $list;
    }
}