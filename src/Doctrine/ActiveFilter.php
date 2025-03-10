<?php

namespace App\Doctrine;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Filter\SQLFilter;

class ActiveFilter extends SQLFilter
{
    public function addFilterConstraint(ClassMetadata $targetEntity, $targetTableAlias): string
    {
        // Vérifie si l'entité a un champ 'status'
        if ($targetEntity->hasField('status')) {
            return sprintf("%s.status = 'active'", $targetTableAlias);
        }

        // Si l'entité n'a pas de champ 'status', on ne modifie pas la requête
        return '';
    }
}

