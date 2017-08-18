<?php
	/****************************************************************************
	* check tickets file, requested by custom AJaX from start page every 30s    *
	* in order to check the directed tickets that might have been opened. This  *
	* returns the HTML to fille the notification in action bar.                 *
	* dependencies: session as $_session (login);                               *
	*               pgsql as $_pgobj (config).                                  *
	*****************************************************************************/

	if(isset($_POST['ajax'])) {
		require("../../config.php");
		require("../../login.php");

		$query = "WITH alm AS (SELECT DISTINCT ON (ticket_id) ticket_id, priority, status, target_id FROM at_ticket_messages ORDER BY ticket_id, date DESC)";
		$query.= " SELECT at.id, at.category, at.subject, at.deadline, alm.priority FROM alm, at_tickets at";
		$query.= " WHERE at.id = alm.ticket_id AND alm.target_id = ". intval($_session->id) ." AND alm.status ORDER BY at.deadline DESC";
		$_pgobj->query($query);
		if($_pgobj->rows) { ?>
								<a class="mail dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
									<i class="fa fa-envelope-o"></i>
									<span class="mail-alert"></span>
								</a>
								<ul class="mail dropdown-menu">
<?php		for($i=0; $i<$_pgobj->rows; $i++) {
				$priority_data = $_settings->ticket_priority[$_pgobj->result[$i]['priority']]; ?>
									<li>
										<a href="./?p=33&tid=<?= $_pgobj->result[$i]['id']; ?>">
											<i class="fa fa-circle" style="color: <?= $priority_data['color']; ?>;" title="<?= $_msg->lang($priority_data['title']); ?>"></i>
											<div class="ellipsis"><?= $_pgobj->result[$i]['subject']; ?></div>
										</a>
									</li>
<?php		} ?>
									</ul>
<?php	}
	} ?>
