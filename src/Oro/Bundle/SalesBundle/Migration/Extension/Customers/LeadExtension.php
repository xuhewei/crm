<?php

namespace Oro\Bundle\SalesBundle\Migration\Extension\Customers;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\EntityBundle\EntityConfig\DatagridScope;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtension;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtensionAwareInterface;
use Oro\Bundle\EntityExtendBundle\Migration\OroOptions;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendDbIdentifierNameGenerator;
use Oro\Bundle\MigrationBundle\Migration\Extension\NameGeneratorAwareInterface;
use Oro\Bundle\MigrationBundle\Tools\DbIdentifierNameGenerator;
use Oro\Bundle\SalesBundle\EntityConfig\CustomerScope;

class LeadExtension implements ExtendExtensionAwareInterface, NameGeneratorAwareInterface
{
    const LEAD_TABLE_NAME = 'orocrm_sales_lead';

    /** @var ExtendExtension */
    protected $extendExtension;

    /** @var ExtendDbIdentifierNameGenerator */
    protected $nameGenerator;

    /**
     * {@inheritdoc}
     */
    public function setExtendExtension(ExtendExtension $extendExtension)
    {
        $this->extendExtension = $extendExtension;
    }

    /**
     * {@inheritdoc}
     */
    public function setNameGenerator(DbIdentifierNameGenerator $nameGenerator)
    {
        $this->nameGenerator = $nameGenerator;
    }

    /**
     * Adds the association between the target customer table and the opportunity table
     *
     * @param Schema $schema
     * @param string $targetTableName  Target entity table name
     * @param string $targetColumnName A column name is used to show related entity
     */
    public function addCustomerAssociation(
        Schema $schema,
        $targetTableName,
        $targetColumnName = null
    ) {
        $table   = $schema->getTable(self::LEAD_TABLE_NAME);
        $targetTable = $schema->getTable($targetTableName);

        if (empty($targetColumnName)) {
            $primaryKeyColumns = $targetTable->getPrimaryKeyColumns();
            $targetColumnName  = reset($primaryKeyColumns);
        }

        $options = new OroOptions();
        $options->set('lead', 'enabled', true);
        $targetTable->addOption(OroOptions::KEY, $options);

        $associationName = ExtendHelper::buildAssociationName(
            $this->extendExtension->getEntityClassByTableName($targetTableName),
            CustomerScope::ASSOCIATION_KIND
        );

        $this->extendExtension->addManyToOneRelation(
            $schema,
            $table,
            $associationName,
            $targetTable,
            $targetColumnName,
            [
                'importexport' => [
                    'excluded' => true,
                ],
                'datagrid' => [
                    'is_visible' => DatagridScope::IS_VISIBLE_FALSE,
                ],
            ]
        );
    }
}
