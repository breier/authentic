<?php
	/*****************************************************************
	* tools ticket edit, requested by custom AJaX from helpdesk page *
	* when saving a different message or deleting it, in order to    *
	* update the Database.                                           *
	******************************************************************/

	if(isset($_POST['ajax']) && isset($_POST['id'])) {
		require("../config.php");
		require("../login.php");
		// --- Case Edit is Update
		if(isset($_POST['message'])) {
			// --- Make sure the message is not uppercase
			$message = pg_escape_string($_POST['message']);
			$upper_message = mb_strtoupper($message, 'UTF-8');
			similar_text($upper_message, $message, $percent);
			if($percent > 40) $message = ucfirst(mb_strtolower($message, 'UTF-8'));
			// --- Prepare query for update
			if($_pgobj->query_params('UPDATE at_ticket_messages SET message = $1 WHERE id = $2', array(trim($message), intval($_POST['id'])))) echo trim($message);
			else echo $_msg->warning("Couldn't update message!");
		}
		// --- Case Edit is Delete
		if(isset($_POST['delete'])) {
			$_pgobj->query_params('SELECT ticket_id FROM at_ticket_messages WHERE id = $1', array(intval($_POST['id'])));
			$ticket_id = ($_pgobj->rows) ? ($_pgobj->result[0]['ticket_id']) : (0);
			$_pgobj->query("SELECT id FROM at_ticket_messages WHERE ticket_id = $ticket_id");
			$messages_amount = $_pgobj->rows;
			if($_pgobj->query_params('DELETE FROM at_ticket_messages WHERE id = $1', array(intval($_POST['id'])))) {
				if($messages_amount == 1) $_pgobj->query("DELETE FROM at_tickets WHERE id = $ticket_id");
				echo "DELETED";
			} else echo $_msg->lang("Couldn't delete message!");
		}
	}
?>
