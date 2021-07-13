<?php

namespace Education\Form\Fieldset;

use Education\Model\Exam as ExamModel;
use Laminas\Form\Fieldset;
use Laminas\InputFilter\InputFilterProviderInterface;
use Laminas\Mvc\I18n\Translator;
use Laminas\Validator\Callback;
use Laminas\Validator\File\Exists;
use Laminas\Validator\Regex;
use Laminas\Validator\StringLength;

class Exam extends Fieldset implements InputFilterProviderInterface
{
    protected $config;

    public function __construct(Translator $translator)
    {
        parent::__construct('exam');

        $this->add(
            [
            'name' => 'file',
            'type' => 'hidden',
            ]
        );

        $this->add(
            [
            'name' => 'course',
            'type' => 'text',
            'options' => [
                'label' => $translator->translate('Course code'),
            ],
            ]
        );

        $this->add(
            [
            'name' => 'date',
            'type' => 'date',
            'options' => [
                'label' => $translator->translate('Exam date'),
            ],
            ]
        );

        $this->add(
            [
            'name' => 'examType',
            'type' => 'Laminas\Form\Element\Select',
            'options' => [
                'label' => $translator->translate('Type'),
                'value_options' => [
                    ExamModel::EXAM_TYPE_FINAL => $translator->translate('Final examination'),
                    ExamModel::EXAM_TYPE_INTERMEDIATE_TEST => $translator->translate('Intermediate test'),
                    ExamModel::EXAM_TYPE_ANSWERS => $translator->translate('Exam answers'),
                    ExamModel::EXAM_TYPE_OTHER => $translator->translate('Other'),
                ],
            ],
            ]
        );

        $this->add(
            [
            'type' => 'Laminas\Form\Element\Select',
            'name' => 'language',
            'options' => [
                'label' => $translator->translate('Language'),
                'value_options' => [
                    ExamModel::EXAM_LANGUAGE_ENGLISH => $translator->translate('English'),
                    ExamModel::EXAM_LANGUAGE_DUTCH => $translator->translate('Dutch'),
                ],
            ],
            ]
        );
    }

    /**
     * Set the configuration.
     *
     * @param array $config
     */
    public function setConfig($config)
    {
        $this->config = $config['education_temp'];
    }

    public function getInputFilterSpecification()
    {
        $dir = $this->config['upload_exam_dir'];

        return [
            'file' => [
                'required' => true,
                'validators' => [
                    [
                        'name' => Regex::class,
                        'options' => [
                            'pattern' => '/.+\.pdf$/',
                        ],
                    ],
                    [
                        'name' => Callback::class,
                        'options' => [
                            'callback' => function ($value) use ($dir) {
                                $validator = new Exists(
                                    [
                                    'directory' => $dir,
                                    ]
                                );

                                return $validator->isValid($value);
                            },
                        ],
                    ],
                ],
            ],

            'course' => [
                'required' => true,
                'validators' => [
                    [
                        'name' => StringLength::class,
                        'options' => [
                            'min' => 5,
                            'max' => 6,
                        ],
                    ],
                    ['name' => 'alnum'],
                ],
                'filters' => [
                    ['name' => 'string_to_upper'],
                ],
            ],

            'date' => [
                'required' => true,
                'validators' => [
                    ['name' => 'date'],
                ],
            ],
        ];
    }
}
