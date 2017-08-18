-- -------------------------------------------
--
-- DEFAULT DATABASE TABLE STRUCTURES
-- Diver : PostGreSQL
-- -------------------------------------------

-- ---------------------------------------------------------------------- --
--                               FUNCTION                                 --
-- ---------------------------------------------------------------------- --

-- --------------------------------------
-- RANDOM PASSWORD
-- --------------------------------------
CREATE OR REPLACE FUNCTION random_password() RETURNS TEXT AS
$$
DECLARE

  /* for OpenWall BCrypt PhPass compatibility
   * ------------------------------------ */
  chars TEXT[] := '{.,/,0,1,2,3,4,5,6,7,8,9,A,B,C,D,E,F,G,H,I,J,K,L,M,N,O,P,Q,R,S,T,U,V,W,X,Y,Z,a,b,c,d,e,f,g,h,i,j,k,l,m,n,o,p,q,r,s,t,u,v,w,x,y,z}';
  result TEXT  := '$2a$08$';
BEGIN
  LOOP
    result := result || chars[1+random()*(array_length(chars, 1)-1)];
    EXIT WHEN length(result) = 60;
  END LOOP;

  RETURN result;
END;
$$ LANGUAGE plpgsql;

-- --------------------------------------
-- RANDOM HEX
-- --------------------------------------

CREATE OR REPLACE FUNCTION random_hex(length INTEGER) RETURNS TEXT AS
$$
DECLARE
  chars TEXT[] := '{0,1,2,3,4,5,6,7,8,9,a,b,c,d,e,f}';
  result TEXT := '';
  i INTEGER := 0;
BEGIN
  IF length < 0 THEN
    RAISE EXCEPTION 'Given length cannot be less than 0';
  END IF;

  FOR i in 1..length LOOP
    result := result || chars[1+random()*(array_length(chars, 1)-1)];
  END LOOP;
  RETURN result;
END;
$$ LANGUAGE plpgsql;

-- --------------------------------------
-- CHANGE AT UPDATE
-- --------------------------------------
CREATE OR REPLACE FUNCTION update_change_updated_at_column()
  RETURNS TRIGGER AS $$
BEGIN
  NEW.updated_at = NOW();
  RETURN NEW;
END;
$$ language 'plpgsql';

CREATE OR REPLACE FUNCTION update_change_role_at_column()
  RETURNS TRIGGER AS $$
BEGIN
  NEW.role = LOWER(trim(NEW.role));
  IF NEW.role == '' THEN
    NEW.role = 'unknown';
  END IF;
  RETURN NEW;
END;
$$ language 'plpgsql';

-- ---------------------------------------------------------------------- --
--                          TABLE DEFINITION                              --
-- ---------------------------------------------------------------------- --

-- --------------------------------------
-- table users
-- --------------------------------------

CREATE TABLE IF NOT EXISTS users (
  id BIGSERIAL PRIMARY KEY,
  username VARCHAR(120) NOT NULL UNIQUE,
  email VARCHAR(255) NOT NULL UNIQUE,
  password VARCHAR(60) DEFAULT random_password(),
  first_name VARCHAR(120) NOT NULL,
  last_name VARCHAR(120) DEFAULT NULL,
  role      VARCHAR(100) DEFAULT 'unknown',
  token_key VARCHAR(128) NOT NULL DEFAULT random_hex(128),
  property  TEXT NOT NULL DEFAULT '',
  created_at TIMESTAMP NOT NULL DEFAULT NOW(),
  updated_at TIMESTAMP NULL DEFAULT '1990-01-01 00:00:00.000000'
);

COMMENT ON COLUMN users.updated_at
  IS 'use 1990-01-01 00:00:00 to prevent sql time stamp zero value';

-- --------------------------------------
-- Triggers for table users
--

DROP TRIGGER IF EXISTS update_change_updated_at_users ON users;
CREATE TRIGGER update_change_updated_at_users BEFORE UPDATE
  ON users FOR EACH ROW EXECUTE PROCEDURE
  update_change_updated_at_column();

-- fixation role

DROP TRIGGER IF EXISTS update_change_role_users ON users;
CREATE TRIGGER update_change_role_users AFTER UPDATE
  ON users FOR EACH ROW EXECUTE PROCEDURE
  update_change_role_at_column();

-- ---------------------------------------------------------------------- --
--                             INSERT DATA                                --
-- ---------------------------------------------------------------------- --

INSERT INTO users(username, email, first_name, role)
    SELECT 'admin', 'admin@example.com', 'Administrator', 'admin:super'
    WHERE NOT EXISTS(
        SELECT FROM users
        WHERE username = 'admin' OR email = 'admin@example.com'
    );


-- --------------------------------------
-- table options
-- --------------------------------------

CREATE TABLE IF NOT EXISTS options (
  id BIGSERIAL PRIMARY KEY,
  options_name VARCHAR(255) NOT NULL UNIQUE,
  options_value TEXT DEFAULT NULL
);

COMMENT ON COLUMN options.options_name
  IS 'Option name must be as unique string.';
