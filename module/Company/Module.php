<?php

namespace Company;

use Company\Form\EditCategory;
use Company\Form\EditCompany;
use Company\Form\EditJob;
use Company\Form\EditLabel;
use Company\Form\EditPackage;
use Company\Mapper\BannerPackage;
use Company\Mapper\Category;
use Company\Mapper\FeaturedPackage;
use Company\Mapper\Job;
use Company\Mapper\Label;
use Company\Mapper\LabelAssignment;
use Company\Mapper\Package;
use Company\Service\Company;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject;

class Module
{
    /**
     * Get the autoloader configuration.
     */
    public function getAutoloaderConfig()
    {
    }

    /**
     * Get the configuration for this module.
     *
     * @return array Module configuration
     */
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    private function getFormFactories()
    {
        return [
            'company_admin_edit_package_form' => function ($sm) {
                return new EditPackage(
                    $sm->get('translator'),
                    "job"
                );
            },
            'company_admin_edit_featuredpackage_form' => function ($sm) {
                return new EditPackage(
                    $sm->get('translator'),
                    "featured"
                );
            },
            'company_admin_edit_category_form' => function ($sm) {
                return new EditCategory(
                    $sm->get('company_mapper_category'),
                    $sm->get('translator'),
                    $sm->get('application_get_languages'),
                    $sm->get('company_hydrator')
                );
            },
            'company_admin_edit_label_form' => function ($sm) {
                return new EditLabel(
                    $sm->get('company_mapper_label'),
                    $sm->get('translator'),
                    $sm->get('application_get_languages'),
                    $sm->get('company_hydrator')
                );
            },
            'company_admin_edit_bannerpackage_form' => function ($sm) {
                return new EditPackage(
                    $sm->get('translator'),
                    "banner"
                );
            },
            'company_admin_edit_company_form' => function ($sm) {
                return new EditCompany(
                    $sm->get('company_mapper_company'),
                    $sm->get('translator')
                );
            },
            'company_admin_edit_job_form' => function ($sm) {
                $form = new EditJob(
                    $sm->get('company_mapper_job'),
                    $sm->get('translator'),
                    $sm->get('application_get_languages'),
                    $sm->get('company_hydrator'),
                    $sm->get('company_service_company')->getLabelList(false)
                );
                $form->setHydrator($sm->get('company_hydrator'));
                return $form;
            },
        ];
    }

    private function getMapperFactories()
    {
        return [
            'company_mapper_company' => function ($sm) {
                return new Mapper\Company(
                    $sm->get('company_doctrine_em')
                );
            },
            'company_mapper_job' => function ($sm) {
                return new Job(
                    $sm->get('company_doctrine_em')
                );
            },
            'company_mapper_package' => function ($sm) {
                return new Package(
                    $sm->get('company_doctrine_em')
                );
            },
            'company_mapper_featuredpackage' => function ($sm) {
                return new FeaturedPackage(
                    $sm->get('company_doctrine_em')
                );
            },
            'company_mapper_category' => function ($sm) {
                return new Category(
                    $sm->get('company_doctrine_em')
                );
            },
            'company_mapper_label' => function ($sm) {
                return new Label(
                    $sm->get('company_doctrine_em')
                );
            },
            'company_mapper_label_assignment' => function ($sm) {
                return new LabelAssignment(
                    $sm->get('company_doctrine_em')
                );
            },
            'company_mapper_bannerpackage' => function ($sm) {
                return new BannerPackage(
                    $sm->get('company_doctrine_em')
                );
            },
        ];
    }

    private function getOtherFactories()
    {
        return [
            'company_doctrine_em' => function ($sm) {
                return $sm->get('doctrine.entitymanager.orm_default');
            },
            'company_language' => function ($sm) {
                return $sm->get('translator');
            },
            'company_hydrator' => function ($sm) {
                return new DoctrineObject(
                    $sm->get('company_doctrine_em')
                );
            },
            'company_acl' => function ($sm) {
                $acl = $sm->get('acl');

                // add resource
                $acl->addResource('company');

                $acl->allow('guest', 'company', 'viewFeaturedCompany');
                $acl->allow('guest', 'company', 'list');
                $acl->allow('guest', 'company', 'view');
                $acl->allow('guest', 'company', 'listVisibleCategories');
                $acl->allow('guest', 'company', 'listVisibleLabels');
                $acl->allow('guest', 'company', 'showBanner');
                $acl->allow('company_admin', 'company', ['insert', 'edit', 'delete']);
                $acl->allow('company_admin', 'company', ['listall', 'listAllCategories', 'listAllLabels']);

                return $acl;
            },
        ];
    }

    /**
     * Get service configuration.
     *
     * @return array Service configuration
     */
    public function getServiceConfig()
    {
        $serviceFactories = [
            'company_service_company' => function ($sm) {
                $translator = $sm->get('translator');
                $userRole = $sm->get('user_role');
                $acl = $sm->get('company_acl');
                $storageService = $sm->get('application_service_storage');
                $companyMapper = $sm->get('company_mapper_company');
                $packageMapper = $sm->get('company_mapper_package');
                $bannerPackageMapper = $sm->get('company_mapper_bannerpackage');
                $featuredPackageMapper = $sm->get('company_mapper_featuredpackage');
                $jobMapper = $sm->get('company_mapper_job');
                $categoryMapper = $sm->get('company_mapper_category');
                $labelMapper = $sm->get('company_mapper_label');
                $labelAssignmentMapper = $sm->get('company_mapper_label_assignment');
                $editCompanyForm = $sm->get('company_admin_edit_company_form');
                $editPackageForm = $sm->get('company_admin_edit_package_form');
                $editBannerPackageForm = $sm->get('company_admin_edit_bannerpackage_form');
                $editFeaturedPackageForm = $sm->get('company_admin_edit_featuredpackage_form');
                $editJobForm = $sm->get('company_admin_edit_job_form');
                $editCategoryForm = $sm->get('company_admin_edit_category_form');
                $editLabelForm = $sm->get('company_admin_edit_label_form');
                $languages = $sm->get('application_get_languages');
                return new Company(
                    $translator,
                    $userRole,
                    $acl,
                    $storageService,
                    $companyMapper,
                    $packageMapper,
                    $bannerPackageMapper,
                    $featuredPackageMapper,
                    $jobMapper,
                    $categoryMapper,
                    $labelMapper,
                    $labelAssignmentMapper,
                    $editCompanyForm,
                    $editPackageForm,
                    $editBannerPackageForm,
                    $editFeaturedPackageForm,
                    $editJobForm,
                    $editCategoryForm,
                    $editLabelForm,
                    $languages
                );
            },
        ];
        $factories = array_merge($serviceFactories, $this->getMapperFactories(), $this->getOtherFactories(), $this->getFormFactories());
        return [
            'factories' => $factories,
        ];
    }
}
