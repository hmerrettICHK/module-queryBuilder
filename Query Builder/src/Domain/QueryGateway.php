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
        $query = $this
            ->newQuery()
            ->from($this->getTableName())
            ->cols([
                'queryBuilderQueryID', 'name', 'type', 'category', 'active', 'gibbonPersonID', 'queryID'
            ])
            ->where(function($query) {
                $query->where("(type='Personal' AND gibbonPersonID=:gibbonPersonID)")
                    ->orWhere("type='School'")
                    ->orWhere("type='gibbonedu.com'");
            })
            ->bindValue('gibbonPersonID', $gibbonPersonID);

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
}
