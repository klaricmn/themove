DROP SCHEMA IF EXISTS features CASCADE;

CREATE SCHEMA features;

ALTER SCHEMA features OWNER TO themove;

SET search_path = features, public;

CREATE TYPE house_type AS ENUM ('detatched', 'townhome', 'apartment');

CREATE TYPE house_verdict AS ENUM ('1-Yes', '2-Maybe', '3-Maybe Not', '4-No');

CREATE TABLE house (
  id serial PRIMARY KEY,

  address varchar(50) NOT NULL,
  address2 varchar(50),
  city varchar(50) NOT NULL,
  state char(2) NOT NULL,
  zipcode integer NOT NULL,

  coords geometry(POINT, 4326),

  n_bed smallint,
  n_bath numeric,
  type house_type,

  notes text,
  url text,

  base_rent money,
  hoa_fee money,
  other_fee money,
  util_incl boolean,

  verdict house_verdict
);

ALTER TABLE house OWNER TO themove;

--GRANT SELECT,INSERT,UPDATE,DELETE ON house to themove;

-------------------------------------------

CREATE TYPE poi_type AS ENUM ('school', 'grocery', 'bigbox', 'other');


CREATE TABLE poi (
  id serial PRIMARY KEY,

  type poi_type,

  name varchar(50) NOT NULL,
  address varchar(50) NOT NULL,
  city varchar(50) NOT NULL,
  state char(2) NOT NULL,
  zipcode integer NOT NULL,

  coords geometry(POINT, 4326),

  notes text,
  url text
);

ALTER TABLE poi OWNER TO themove;

\copy poi (name,address,city,state,zipcode) from 'schools.txt'
UPDATE poi SET type = 'school' WHERE type IS NULL;

\copy poi (name,address,city,state,zipcode) from 'grocery.txt'
UPDATE poi SET type = 'grocery' WHERE type IS NULL;

\copy poi (name,address,city,state,zipcode) from 'bigbox.txt'
UPDATE poi SET type = 'bigbox' WHERE type IS NULL;

ALTER TABLE poi ALTER COLUMN type SET NOT NULL;

--GRANT SELECT,INSERT,UPDATE,DELETE ON poi to themove;

-----------------------

-- some sample data
INSERT INTO house (address,city,state,zipcode,coords) VALUES ('123 Fake St', 'Somewhere', 'VA', 22150, ST_SetSRID(ST_MakePoint( -77.179444, 38.788611 ), 4326));
INSERT INTO house (address,city,state,zipcode,coords) VALUES ('666 Elm St', 'Hell', 'VA', 22150, ST_SetSRID(ST_MakePoint( -77.279444,  38.798611 ), 4326));
