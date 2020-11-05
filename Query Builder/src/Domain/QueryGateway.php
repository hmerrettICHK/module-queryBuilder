<?php
/*
Gibbon, Flexible & Open School System
Copyright (C) 2010, Ross Parker

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program. If not, see <http://www.gnu.org/licenses/>.
*/

namespace Gibbon\Module\QueryBuilder\Domain;

use Gibbon\Domain\Traits\TableAware;
use Gibbon\Domain\QueryCriteria;
use Gibbon\Domain\QueryableGateway;

/**
 * @version v16
 * @since   v16
 */
class QueryGateway extends QueryableGateway
{
    use TableAware;

    private static $tableName = 'queryBuilderQuery';
    private static $primaryKey = 'queryBuilderQueryID';

    private static $searchableColumns = ['name', 'category'];

    /**
     * @param QueryCriteria $criteria
     * @return DataSet
     */
    public function queryQueries(QueryCriteria $criteria, $gibbonPersonID)
    {
        $data = ['gibbonPersonID' => $gibbonPersonID];
        $gibbonRoleIDAll = $this->db()->selectOne('SELECT gibbonRoleIDAll FROM gibbonPerson WHERE gibbonPersonID=:gibbonPersonID', $data);

        $query = $this
            ->newQuery()
            ->cols([
                'queryBuilderQueryID', 'name', 'type', 'category', 'active', 'gibbonPersonID', 'queryID', 'queryBuilderQuery.actionName', 'queryBuilderQuery.moduleName', 'permission.permissionID'
            ])
            ->from($this->getTableName())
            ->joinSubSelect(                     
                'LEFT',                     
                'SELECT gibbonPermission.permissionID, gibbonRole.gibbonRoleID, gibbonAction.name as actionName, gibbonModule.name as moduleName
                FROM gibbonModule
                JOIN gibbonAction ON (gibbonModule.gibbonModuleID=gibbonAction.gibbonModuleID) 
                JOIN gibbonPermission ON (gibbonPermission.gibbonActionID=gibbonAction.gibbonActionID)
                JOIN gibbonRole ON (gibbonRole.gibbonRoleID=gibbonPermission.gibbonRoleID)',                  
                'permission',                   
                "(permission.actionName=queryBuilderQuery.actionName OR permission.actionName LIKE CONCAT(queryBuilderQuery.actionName, '_%')) AND permission.moduleName=queryBuilderQuery.moduleName AND FIND_IN_SET(permission.gibbonRoleID, :gibbonRoleIDAll)"
            )
            ->where(function($query) {
                $query->where("(type='Personal' AND gibbonPersonID=:gibbonPersonID)")
                    ->orWhere("type='School'")
                    ->orWhere("type='gibbonedu.com'");
            })
            ->bindValue('gibbonPersonID', $gibbonPersonID)
            ->bindValue('gibbonRoleIDAll', $gibbonRoleIDAll)
            ->having("((actionName IS NULL OR actionName = '') OR (actionName IS NOT NULL AND permissionID IS NOT NULL))")
            ->groupBy(['queryBuilderQuery.queryBuilderQueryID']);

        return $this->runQuery($query, $criteria);
    }

    public function syncRemoveQueries($queries)
    {
        $queryIDs = array_map(function($query) use ($queries) {
                        return intval($query["queryID"]);
                    }, $queries);

        $queryIDList = implode(",", $queryIDs);

        $sql = "DELETE FROM queryBuilderQuery WHERE type='gibbonedu.com' AND queryID NOT IN ($queryIDList)";
        return $this->db()->delete($sql);
    }

    public function selectActionListByPerson($gibbonPersonID)
    {
        $data = ['gibbonPersonID' => $gibbonPersonID];
        $sql = "(
            SELECT gibbonModule.name as groupBy, CONCAT(gibbonModule.name, ':', SUBSTRING_INDEX(gibbonAction.name, '_', 1)) as value, CONCAT(SUBSTRING_INDEX(gibbonAction.name, '_', 1), ' (grouped)') as name 
                FROM gibbonPerson
                JOIN gibbonRole ON (gibbonPerson.gibbonRoleIDPrimary=gibbonRole.gibbonRoleID OR FIND_IN_SET(gibbonRole.gibbonRoleID, gibbonPerson.gibbonRoleIDAll))
                JOIN gibbonPermission ON (gibbonRole.gibbonRoleID=gibbonPermission.gibbonRoleID)
                JOIN gibbonAction ON (gibbonPermission.gibbonActionID=gibbonAction.gibbonActionID)
                JOIN gibbonModule ON (gibbonModule.gibbonModuleID=gibbonAction.gibbonModuleID) 
                WHERE gibbonPerson.gibbonPersonID=:gibbonPersonID
                AND gibbonAction.name LIKE '%\_%'
            ) UNION ALL (
                SELECT gibbonModule.name as groupBy, CONCAT(gibbonModule.name, ':', gibbonAction.name) as value, gibbonAction.name as name 
                FROM gibbonPerson
                JOIN gibbonRole ON (gibbonPerson.gibbonRoleIDPrimary=gibbonRole.gibbonRoleID OR FIND_IN_SET(gibbonRole.gibbonRoleID, gibbonPerson.gibbonRoleIDAll))
                JOIN gibbonPermission ON (gibbonRole.gibbonRoleID=gibbonPermission.gibbonRoleID)
                JOIN gibbonAction ON (gibbonPermission.gibbonActionID=gibbonAction.gibbonActionID)
                JOIN gibbonModule ON (gibbonModule.gibbonModuleID=gibbonAction.gibbonModuleID) 
                WHERE gibbonPerson.gibbonPersonID=:gibbonPersonID
                
            ) ORDER BY groupBy, name" ;

        return $this->db()->select($sql, $data);
    }

    public function getIsQueryAccessible($queryBuilderQueryID, $gibbonPersonID)
    {
        $data = ['queryBuilderQueryID' => $queryBuilderQueryID, 'gibbonPersonID' => $gibbonPersonID];
        $sql = "SELECT gibbonModule.name as groupBy, CONCAT(gibbonModule.name, ':', gibbonAction.name) as value, gibbonAction.name as name 
                FROM queryBuilderQuery
                JOIN gibbonModule ON (gibbonModule.name=queryBuilderQuery.moduleName) 
                JOIN gibbonAction ON ((gibbonAction.name=queryBuilderQuery.actionName OR gibbonAction.name LIKE CONCAT(queryBuilderQuery.actionName, '_%')) AND gibbonAction.gibbonModuleID=gibbonModule.gibbonModuleID) 
                JOIN gibbonPermission ON (gibbonPermission.gibbonActionID=gibbonAction.gibbonActionID)
                JOIN gibbonPerson ON (FIND_IN_SET(gibbonPermission.gibbonRoleID, gibbonPerson.gibbonRoleIDAll))
                WHERE queryBuilderQuery.queryBuilderQueryID=:queryBuilderQueryID
                AND gibbonPerson.gibbonPersonID=:gibbonPersonID" ;

        return $this->db()->selectOne($sql, $data);
    }
}
