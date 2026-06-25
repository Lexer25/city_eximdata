<?php defined('SYSPATH') or die('No direct script access.');

class EximdataExport {

    /** @var Database */
    private $db;

    public function __construct() {
        $this->db = Database::instance('fb');
    }

    public function exportPeople($id_org) {
        $sql = 'SELECT P.ID_PEP, P.SURNAME, P.NAME, P.PATRONYMIC, P.NOTE, C.ID_CARD, C.ID_CARDTYPE
                FROM PEOPLE P
                JOIN CARD C ON C.ID_PEP = P.ID_PEP
                WHERE P.ID_ORG = :id_org';

        return DB::query(Database::SELECT, $sql)
            ->param(':id_org', $id_org)
            ->execute($this->db)
            ->as_array();
    }

    public function exportTree($id_org) {
        $timestamp = time();
        $uid = $this->generateUid();

        return array(
            'about' => array(
                'timestamp' => $timestamp,
                'datestamp' => date('d.m.Y H:i:s', $timestamp),
                'uid' => $uid,
            ),
            'org' => $this->getOrganizationTree($id_org),
            'people' => $this->exportPeopleFromParentOrg($id_org),
            'card' => $this->exportCardsFromParentOrg($id_org),
        );
    }

    private function getOrganizationTree($id_org) {
        $sql = 'SELECT OG.ID_ORG, OG.NAME, OG.ID_PARENT, OG.FLAG 
                FROM ORGANIZATION_GETCHILD(1, :id_org) OG 
                ORDER BY OG.NAME';

        $result = array();
        $rows = DB::query(Database::SELECT, $sql)
            ->param(':id_org', $id_org)
            ->execute($this->db)
            ->as_array();

        foreach ($rows as $row) {
            $id = (int)$row['ID_ORG'];
            $result[$id] = array(
                'id' => $id,
                'title' => iconv('windows-1251', 'UTF-8', $row['NAME']),
                'parent' => (int)(isset($row['ID_PARENT']) ? $row['ID_PARENT'] : 0),
                'busy' => isset($row['FLAG']) ? $row['FLAG'] : null,
            );
        }

        if (isset($result[$id_org])) {
            $result[$id_org]['parent'] = 0;
        }

        return $result;
    }

    private function exportPeopleFromParentOrg($id_org) {
        $sql = 'SELECT P.ID_PEP, P.ID_ORG, P.NAME, P.SURNAME, P.PATRONYMIC, 
                       P.PHONEWORK, P.NOTE, P.POST, P.TABNUM 
                FROM ORGANIZATION_GETCHILD(1, :id_org) OG
                JOIN PEOPLE P ON P.ID_ORG = OG.ID_ORG
                WHERE P."ACTIVE" > 0';

        return DB::query(Database::SELECT, $sql)
            ->param(':id_org', $id_org)
            ->execute($this->db)
            ->as_array();
    }

    private function exportCardsFromParentOrg($id_org) {
        $sql = 'SELECT C.ID_CARD, C.ID_PEP, P.TABNUM, C.TIMESTART, C.TIMEEND, 
                       C.NOTE, C.STATUS, C."ACTIVE", C.FLAG, C.ID_CARDTYPE
                FROM ORGANIZATION_GETCHILD(1, :id_org) OG
                JOIN PEOPLE P ON P.ID_ORG = OG.ID_ORG
                JOIN CARD C ON C.ID_PEP = P.ID_PEP
                WHERE P."ACTIVE" > 0';

        return DB::query(Database::SELECT, $sql)
            ->param(':id_org', $id_org)
            ->execute($this->db)
            ->as_array();
    }

    private function generateUid() {
        return '{' . substr(strtoupper(md5(uniqid(rand(), true))), 0, 8) . '}';
    }
}
