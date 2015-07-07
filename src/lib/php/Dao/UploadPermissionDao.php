<?php
/*
Copyright (C) 2015, Siemens AG

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
version 2 as published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License along
with this program; if not, write to the Free Software Foundation, Inc.,
51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

namespace Fossology\Lib\Dao;

use Fossology\Lib\Auth\Auth;
use Fossology\Lib\Util\Object;
use Monolog\Logger;


class UploadPermissionDao extends Object
{
  /** @var DbManager */
  private $dbManager;
  /** @var Logger */
  private $logger;

  public function __construct(DbManager $dbManager, Logger $logger)
  {
    $this->dbManager = $dbManager;
    $this->logger = $logger;
  }
  
  public function isAccessible($uploadId, $groupId) 
  {
    $perm = $this->dbManager->getSingleRow('SELECT perm FROM perm_upload WHERE upload_fk=$1 AND group_fk=$2',
        array($uploadId, $groupId), __METHOD__);
    return $perm['perm']>Auth::PERM_NONE;
  }
  
  public function isEditable($uploadId, $groupId) 
  {
    if ($_SESSION[Auth::USER_LEVEL] == PLUGIN_DB_ADMIN) {
      return true;
    }

    $perm = $this->dbManager->getSingleRow('SELECT perm FROM perm_upload WHERE upload_fk=$1 AND group_fk=$2',
        array($uploadId, $groupId), __METHOD__);
    return $perm['perm']>=Auth::PERM_WRITE;
  }

  public function makeAccessibleToGroup($uploadId, $groupId, $perm=null)
  {
    if (null === $perm) {
      $perm = Auth::PERM_ADMIN;
    }
    $this->dbManager->getSingleRow("INSERT INTO perm_upload (perm, upload_fk, group_fk) "
            . " VALUES($1,$2,$3)",
               array($perm, $uploadId, $groupId), __METHOD__);
  }

  public function makeAccessibleToAllGroupsOf($uploadId, $userId, $perm=null)
  {
    if (null === $perm) {
      $perm = Auth::PERM_ADMIN;
    }
    $this->dbManager->getSingleRow("INSERT INTO perm_upload (perm, upload_fk, group_fk) "
            . "SELECT $1 perm, $2 upload_fk, gum.group_fk"
            . " FROM group_user_member gum LEFT JOIN perm_upload ON perm_upload.group_fk=gum.group_fk AND upload_fk=$2"
            . " WHERE perm_upload IS NULL AND gum.user_fk=$3",
               array($perm, $uploadId, $userId), __METHOD__.'.insert');
  }
 

}