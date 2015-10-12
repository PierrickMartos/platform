<?php

namespace Oro\Bundle\DataGridBundle\Extension\InlineEditing\EntityManager;

use Doctrine\Bundle\DoctrineBundle\Registry;

use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;

class FormBuilder
{
    /** @var FormFactory */
    protected $formFactory;

    /** @var Registry */
    protected $registry;

    /**
     * @param FormFactory $formFactory
     * @param Registry $registry
     */
    public function __construct(
        FormFactory $formFactory,
        Registry $registry
    ) {
        $this->formFactory = $formFactory;
        $this->registry = $registry;
    }

    /**
     * @param $entity
     * @return Form
     */
    public function getForm($entity)
    {
        $form = $this->formFactory->createBuilder('form', $entity, array('csrf_protection' => false))
            ->getForm();

        return $form;
    }

    /**
     * @param FormInterface $form
     * @param $entity
     * @param $fieldName
     *
     * @return FormInterface
     */
    public function add(FormInterface $form, $entity, $fieldName)
    {
        $fieldType = $this->getAssociationType($entity, $fieldName);
        $form = $form->add($fieldName, $fieldType);

        return $form;
    }

    /**
     * @param $entity
     * @param $fieldName
     * @return string
     */
    protected function getAssociationType($entity, $fieldName)
    {
        $className = get_class($entity);
        $em = $this->registry->getManager();
        $metaData = $em->getClassMetadata($className);
        $accessor = PropertyAccess::createPropertyAccessor();
        $fieldInfo = $accessor->getValue($metaData->fieldMappings, '['.$fieldName.']');
        $fieldType = $fieldInfo['type'];

        switch ($fieldType) {
            case 'string':
                $type = 'text';
                break;
            case 'datetime':
                $type = 'oro_datetime';
                break;
            default:
                $type = $fieldType;
                break;
        }

        return $type;
    }
}
