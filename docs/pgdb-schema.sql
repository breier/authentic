--
-- pgdb-schema.sql
--

CREATE FUNCTION at_date_update() RETURNS trigger
   LANGUAGE plpgsql
   AS $$BEGIN
   NEW.date = now();
   RETURN NEW;
END;$$;

CREATE FUNCTION at_duplicated_accounts_update() RETURNS trigger
   LANGUAGE plpgsql
   AS $$BEGIN
      UPDATE radacct SET acctstoptime = now()
      WHERE acctstoptime IS NULL
      AND username IN (SELECT username FROM at_duplicated_active_accounts)
      AND acctstarttime = (SELECT acctstarttime FROM radacct
         WHERE acctstoptime IS NULL AND username IN (SELECT username FROM at_duplicated_active_accounts)
         ORDER BY acctstarttime LIMIT 1);
   RETURN NULL;
END;$$;

CREATE FUNCTION save_deleted() RETURNS trigger
   LANGUAGE plpgsql
   AS $$DECLARE
      table_name text;
      query_op text;
   BEGIN
      table_name := TG_TABLE_NAME || '_deleted';
      query_op := 'INSERT INTO ' || table_name || ' VALUES (($1).*)';
      EXECUTE query_op USING OLD;
   RETURN OLD;
END;$$;

CREATE VIEW at_check AS
   SELECT rcp.username, rcp.value AS password, rcp.attribute AS passtype, rcm.value AS mac_address
   FROM (radcheck rcp LEFT JOIN radcheck rcm ON rcp.username = rcm.username AND rcm.attribute = 'Calling-Station-Id')
   WHERE (rcp.attribute IN ('Cleartext-Password', 'User-Password')) ORDER BY rcp.username;

CREATE TABLE at_equipments (
   id serial PRIMARY KEY NOT NULL,
   date timestamp without time zone DEFAULT now() NOT NULL,
   brand_name text NOT NULL,
   service_type text NOT NULL,
   service_port integer NOT NULL,
   username text NOT NULL,
   password text NOT NULL,
   category text NOT NULL,
   groupname text NOT NULL,
   ip_address inet NOT NULL,
   mac_address macaddr NOT NULL,
   location text,
   comments text
);

CREATE TABLE at_monitor (
   id serial PRIMARY KEY NOT NULL,
   equipment_id integer NOT NULL,
   equipment_name text,
   date timestamp without time zone DEFAULT now() NOT NULL,
   data text NOT NULL
);

CREATE TABLE at_session (
   id serial PRIMARY KEY NOT NULL,
   date timestamp without time zone DEFAULT now() NOT NULL,
   username text,
   php_session_id text NOT NULL,
   status boolean NOT NULL,
   ip_address inet,
   mac_address macaddr,
   connection integer[]
);

CREATE TABLE at_settings (
   id serial PRIMARY KEY NOT NULL,
   date timestamp without time zone DEFAULT now() NOT NULL,
   category text NOT NULL,
   data text NOT NULL,
   label text,
   sequence integer
);

CREATE TABLE at_tickets (
   id serial PRIMARY KEY NOT NULL,
   customer_id integer,
   category integer NOT NULL,
   subject text NOT NULL,
   deadline timestamp without time zone NOT NULL
);

CREATE TABLE at_ticket_messages (
   id serial PRIMARY KEY NOT NULL,
   date timestamp without time zone DEFAULT now() NOT NULL,
   ticket_id integer NOT NULL,
   user_id integer NOT NULL,
   priority integer NOT NULL,
   message text NOT NULL,
   status boolean NOT NULL DEFAULT TRUE
);

CREATE TABLE at_ticket_sitrep (
   id integer PRIMARY KEY NOT NULL,
   latency integer[],
   throughput integer[],
   internal_address inet,
   internal_dns inet
);

CREATE TABLE at_plans (
	id serial PRIMARY KEY NOT NULL,
	name text NOT NULL,
	media text NOT NULL,
	price real not NULL
);

CREATE TABLE at_onts (
	service_port integer PRIMARY KEY NOT NULL,
	gpon_slot text NOT NULL,
	gpon_port integer NOT NULL,
	ont_id integer NOT NULL,
	ont_sn text NOT NULL,
	ont_wan_mode text NOT NULL,
	date timestamp without time zone DEFAULT now() NOT NULL
);

CREATE TABLE at_userdata (
   id serial PRIMARY KEY NOT NULL,
   date timestamp without time zone DEFAULT now() NOT NULL,
   username text,
   phone integer,
   higher_id integer NOT NULL,
   data text NOT NULL,
   connection text,
   picture text
);

CREATE TABLE at_userdata_deleted (
   id integer,
   date timestamp without time zone,
   username text,
   phone integer,
   higher_id integer,
   data text,
   connection text,
   picture text
);

CREATE VIEW at_technicians AS
   SELECT at_userdata.id, "substring"(at_userdata.data, ':"name";s:[0-9]+:"([^"]+)";') AS name
   FROM at_userdata
   WHERE (at_userdata.username IN (SELECT radusergroup.username FROM radusergroup
      WHERE radusergroup.groupname IN ('full', 'admn', 'tech')))
   UNION SELECT 0 AS id, 'unknown' AS name;

CREATE VIEW at_duplicated_active_accounts AS
   SELECT accounting.username, accounting.dup
   FROM (SELECT radacct.username, count(radacct.username) AS dup FROM radacct
      WHERE (radacct.acctstoptime IS NULL) GROUP BY radacct.username) accounting
   WHERE (accounting.dup > 1);

CREATE VIEW at_framedipaddress_accounts AS
   SELECT rug.username, ra.framedipaddress
   FROM (radusergroup rug LEFT OUTER JOIN radacct ra ON rug.username = ra.username AND ra.acctstoptime IS NULL)
   WHERE rug.groupname <> 'full' ORDER BY rug.username;

CREATE TRIGGER save_customers BEFORE DELETE ON at_userdata FOR EACH ROW EXECUTE PROCEDURE save_deleted();
CREATE TRIGGER at_date_session_update BEFORE UPDATE ON at_session FOR EACH ROW EXECUTE PROCEDURE at_date_update();
CREATE TRIGGER at_duplicated_active_accounts_update AFTER INSERT ON radacct FOR EACH STATEMENT EXECUTE PROCEDURE at_duplicated_accounts_update();
