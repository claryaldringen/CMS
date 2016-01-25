<?php

namespace cms;

class ConcertModel extends BaseModel
{
	public function getConcerts($menuId, $limit = 1000, $format = false) {

		$languageId = $this->languageId;

		$sql = "SELECT c.id,c.menu_id,c.start_time,c.ticket_uri,t1.text AS name,t2.text AS place,t3.text,i.hash AS image FROM concert c
			JOIN name_has_text nht1 ON nht1.name_id=c.name_id AND nht1.language_id=%i
			JOIN text t1 ON t1.id=nht1.text_id
			JOIN name_has_text nht2 ON nht2.name_id=c.place_name_id AND nht2.language_id=%i
			JOIN text t2 ON t2.id=nht2.text_id
			JOIN name_has_text nht3 ON nht3.name_id=c.text_name_id AND nht3.language_id=%i
			JOIN text t3 ON t3.id=nht3.text_id
			LEFT JOIN image i ON i.id=c.image_id
			WHERE menu_id=%i
			ORDER BY start_time ASC
			LIMIT %i";

		$rows = $this->db->query($sql, $languageId, $languageId, $languageId, $menuId, $limit)->fetchAll();
		if($format) {
			foreach($rows as &$row) {
				if(date('H', strtotime($row['start_time'])) > 2) $row['start_time'] = date('d.m.Y H:i', strtotime($row['start_time']));
				else  $row['start_time'] = date('d.m.Y', strtotime($row['start_time']));
			}
		}

		return $rows;
	}

}