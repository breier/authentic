--
-- pgdb-schema.sql
--

CREATE FUNCTION at_date_update() RETURNS trigger
   LANGUAGE plpgsql
   AS $$BEGIN
   NEW.date = now();
   RETURN NEW;
END;$$;

CREATE FUNCTION at_account_groupname() RETURNS trigger
   LANGUAGE plpgsql
   AS $$BEGIN
   NEW.groupname := (SELECT groupname FROM radusergroup WHERE username = NEW.username ORDER BY priority LIMIT 1);
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

CREATE VIEW at_userauth AS
	WITH rap AS (SELECT username, "value" AS password FROM radcheck WHERE attribute = 'Cleartext-Password'),
	ram AS (SELECT username, "value" AS mac_address FROM radcheck WHERE attribute = 'Calling-Station-Id')
	SELECT rug.username, array_agg(rug.groupname) AS groupname, array_agg(rug.priority) AS priority, rap.password, ram.mac_address
	FROM radusergroup rug LEFT JOIN rap ON rug.username = rap.username LEFT OUTER JOIN ram ON rap.username = ram.username
	GROUP BY rug.username, ram.mac_address, rap.password;

CREATE VIEW at_technicians AS
   SELECT id, "substring"(data, ':"name";s:[0-9]+:"([^"]+)";') AS name
   FROM at_userdata
   WHERE (username IN (SELECT username FROM at_userauth WHERE groupname && ARRAY['admn', 'tech'] AND NOT groupname && ARRAY['disabled']))
   UNION SELECT 0 AS id, 'unknown' AS name;

CREATE VIEW at_duplicated_active_accounts AS
   SELECT accounting.username, accounting.dup
   FROM (SELECT radacct.username, count(radacct.username) AS dup FROM radacct
      WHERE (radacct.acctstoptime IS NULL) GROUP BY radacct.username) accounting
   WHERE (accounting.dup > 1);

CREATE VIEW at_framedipaddress_accounts AS
   SELECT aua.username, ra.framedipaddress
   FROM at_userauth aua LEFT OUTER JOIN radacct ra ON aua.username = ra.username AND ra.acctstoptime IS NULL
   WHERE NOT aua.groupname && ARRAY['full'] ORDER BY aua.username;

CREATE TRIGGER save_customers BEFORE DELETE ON at_userdata FOR EACH ROW EXECUTE PROCEDURE save_deleted();
CREATE TRIGGER at_date_session_update BEFORE UPDATE ON at_session FOR EACH ROW EXECUTE PROCEDURE at_date_update();
CREATE TRIGGER at_account_groupname_add BEFORE INSERT ON radacct FOR EACH ROW EXECUTE PROCEDURE at_account_groupname();
CREATE TRIGGER at_duplicated_active_accounts_update AFTER INSERT ON radacct FOR EACH STATEMENT EXECUTE PROCEDURE at_duplicated_accounts_update();
