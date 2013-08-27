--
-- PostgreSQL database dump
--

SET statement_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SET check_function_bodies = false;
SET client_min_messages = warning;

--
-- Name: plpgsql; Type: EXTENSION; Schema: -; Owner: 
--

CREATE EXTENSION IF NOT EXISTS plpgsql WITH SCHEMA pg_catalog;


--
-- Name: EXTENSION plpgsql; Type: COMMENT; Schema: -; Owner: 
--

COMMENT ON EXTENSION plpgsql IS 'PL/pgSQL procedural language';


SET search_path = public, pg_catalog;

--
-- Name: catalogtype; Type: TYPE; Schema: public; Owner: postgres
--

CREATE TYPE catalogtype AS ENUM (
    'local',
    'remote'
);


ALTER TYPE public.catalogtype OWNER TO postgres;

--
-- Name: mode; Type: TYPE; Schema: public; Owner: postgres
--

CREATE TYPE mode AS ENUM (
    'abr',
    'vbr',
    'cbr'
);


ALTER TYPE public.mode OWNER TO postgres;

SET default_tablespace = '';

SET default_with_oids = false;

--
-- Name: api_key; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE api_key (
    id integer NOT NULL,
    groupid integer NOT NULL,
    accesskeyid character varying(120) NOT NULL,
    accesssecret character varying(120) NOT NULL,
    consumerkey character varying(120) NOT NULL,
    active boolean DEFAULT true
);


ALTER TABLE public.api_key OWNER TO postgres;

--
-- Name: api_key_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE api_key_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.api_key_id_seq OWNER TO postgres;

--
-- Name: api_key_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE api_key_id_seq OWNED BY api_key.id;


--
-- Name: catalog; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE catalog (
    id integer NOT NULL,
    groupid integer NOT NULL,
    name character varying(126) NOT NULL,
    path character varying(245) NOT NULL,
    catalogtype catalogtype DEFAULT 'local'::catalogtype NOT NULL,
    renamepattern character varying(245),
    enabled boolean DEFAULT true NOT NULL,
    cleaned timestamp without time zone,
    modified timestamp without time zone DEFAULT '2013-05-05 22:06:59.362954'::timestamp without time zone NOT NULL,
    created timestamp without time zone DEFAULT '2013-05-05 22:06:59.362954'::timestamp without time zone NOT NULL
);


ALTER TABLE public.catalog OWNER TO postgres;

--
-- Name: catalog_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE catalog_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.catalog_id_seq OWNER TO postgres;

--
-- Name: catalog_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE catalog_id_seq OWNED BY catalog.id;


--
-- Name: detail; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE detail (
    id integer NOT NULL,
    userid integer NOT NULL,
    firstname character varying(120) NOT NULL,
    lastname character varying(120) NOT NULL,
    dob date,
    organisation character varying(120) DEFAULT ''::character varying NOT NULL,
    alias character varying(45) DEFAULT ''::character varying NOT NULL,
    addressline1 character varying(120) NOT NULL,
    addressline2 character varying(120) DEFAULT ''::character varying NOT NULL,
    addressline3 character varying(120) DEFAULT ''::character varying NOT NULL,
    addressline4 character varying(120) DEFAULT ''::character varying NOT NULL,
    postcode character varying(45) NOT NULL,
    country character varying(60) NOT NULL,
    telephone1 character varying(66) DEFAULT ''::character varying NOT NULL,
    telephone2 character varying(66) DEFAULT ''::character varying NOT NULL,
    image1 character varying(245) DEFAULT ''::character varying NOT NULL,
    image2 character varying(245) DEFAULT ''::character varying NOT NULL,
    description1 character varying(245) DEFAULT ''::character varying NOT NULL,
    description2 character varying(245) DEFAULT ''::character varying NOT NULL
);


ALTER TABLE public.detail OWNER TO postgres;

--
-- Name: detail_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE detail_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.detail_id_seq OWNER TO postgres;

--
-- Name: detail_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE detail_id_seq OWNED BY detail.id;


--
-- Name: epg; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE epg (
    id character varying(120) NOT NULL,
    stream_uri text,
    title character varying(120) NOT NULL,
    sub_title character varying(120) DEFAULT ''::character varying NOT NULL,
    episode character varying(120) DEFAULT ''::character varying NOT NULL,
    text character varying(245) DEFAULT ''::character varying NOT NULL,
    start timestamp without time zone NOT NULL,
    duration integer NOT NULL,
    service character varying(45) NOT NULL,
    channel character varying(45) NOT NULL
);


ALTER TABLE public.epg OWNER TO postgres;

--
-- Name: group; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE "group" (
    id integer NOT NULL,
    owner integer NOT NULL,
    name character varying(45) NOT NULL,
    active boolean DEFAULT true NOT NULL
);


ALTER TABLE public."group" OWNER TO postgres;

--
-- Name: group_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE group_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.group_id_seq OWNER TO postgres;

--
-- Name: group_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE group_id_seq OWNED BY "group".id;


--
-- Name: group_member; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE group_member (
    id integer NOT NULL,
    groupid integer NOT NULL,
    userid integer NOT NULL,
    relatedto integer NOT NULL,
    accepted boolean DEFAULT false NOT NULL
);


ALTER TABLE public.group_member OWNER TO postgres;

--
-- Name: group_member_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE group_member_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.group_member_id_seq OWNER TO postgres;

--
-- Name: group_member_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE group_member_id_seq OWNED BY group_member.id;


--
-- Name: live_stream; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE live_stream (
    id integer NOT NULL,
    name character varying(255) NOT NULL,
    url character varying(255) NOT NULL,
    genre integer NOT NULL,
    catalogid integer NOT NULL,
    frequency character varying(32)
);


ALTER TABLE public.live_stream OWNER TO postgres;

--
-- Name: live_stream_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE live_stream_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.live_stream_id_seq OWNER TO postgres;

--
-- Name: live_stream_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE live_stream_id_seq OWNED BY live_stream.id;


--
-- Name: policy; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE policy (
    id integer NOT NULL,
    groupid integer NOT NULL,
    name character varying(120) NOT NULL,
    namespacekey character varying(120) NOT NULL,
    comment character varying(245) NOT NULL
);


ALTER TABLE public.policy OWNER TO postgres;

--
-- Name: policy_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE policy_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.policy_id_seq OWNER TO postgres;

--
-- Name: policy_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE policy_id_seq OWNED BY policy.id;


--
-- Name: privilege; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE privilege (
    id integer NOT NULL,
    name character varying(45) NOT NULL
);


ALTER TABLE public.privilege OWNER TO postgres;

--
-- Name: privilege_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE privilege_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.privilege_id_seq OWNER TO postgres;

--
-- Name: privilege_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE privilege_id_seq OWNED BY privilege.id;


--
-- Name: resource; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE resource (
    id integer NOT NULL,
    parentid integer,
    name character varying(120) NOT NULL,
    description character varying(245) DEFAULT ''::character varying NOT NULL
);


ALTER TABLE public.resource OWNER TO postgres;

--
-- Name: resource_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE resource_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.resource_id_seq OWNER TO postgres;

--
-- Name: resource_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE resource_id_seq OWNED BY resource.id;


--
-- Name: rule; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE rule (
    id integer NOT NULL,
    groupid integer NOT NULL,
    resourceid integer NOT NULL,
    privilegeid integer NOT NULL,
    allow boolean DEFAULT true NOT NULL
);


ALTER TABLE public.rule OWNER TO postgres;

--
-- Name: rule_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE rule_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.rule_id_seq OWNER TO postgres;

--
-- Name: rule_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE rule_id_seq OWNED BY rule.id;


--
-- Name: shadow; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE shadow (
    id integer NOT NULL,
    username character varying(45) NOT NULL,
    password character varying(120) NOT NULL,
    salt character varying(245) NOT NULL,
    email character varying(120),
    active boolean DEFAULT false NOT NULL,
    admin boolean DEFAULT false NOT NULL,
    organisation boolean DEFAULT false NOT NULL,
    service boolean DEFAULT false NOT NULL,
    banned boolean DEFAULT false NOT NULL,
    restkey character varying(120) DEFAULT ''::character varying NOT NULL,
    resetexpires timestamp without time zone DEFAULT '2013-04-21 09:57:27.308314'::timestamp without time zone NOT NULL
);


ALTER TABLE public.shadow OWNER TO postgres;

--
-- Name: shadow_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE shadow_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.shadow_id_seq OWNER TO postgres;

--
-- Name: shadow_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE shadow_id_seq OWNED BY shadow.id;


--
-- Name: video; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE video (
    id integer NOT NULL,
    catalogid integer NOT NULL,
    name character varying(255) NOT NULL,
    size integer NOT NULL,
    hash character varying(255) NOT NULL,
    bitrate integer NOT NULL,
    mode mode,
    "time" integer NOT NULL,
    modified timestamp without time zone DEFAULT '2013-05-05 22:47:42.506874'::timestamp without time zone NOT NULL,
    created timestamp without time zone DEFAULT '2013-05-05 22:47:42.506874'::timestamp without time zone NOT NULL
);


ALTER TABLE public.video OWNER TO postgres;

--
-- Name: video_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE video_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.video_id_seq OWNER TO postgres;

--
-- Name: video_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE video_id_seq OWNED BY video.id;


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY api_key ALTER COLUMN id SET DEFAULT nextval('api_key_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY catalog ALTER COLUMN id SET DEFAULT nextval('catalog_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY detail ALTER COLUMN id SET DEFAULT nextval('detail_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY "group" ALTER COLUMN id SET DEFAULT nextval('group_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY group_member ALTER COLUMN id SET DEFAULT nextval('group_member_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY live_stream ALTER COLUMN id SET DEFAULT nextval('live_stream_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY policy ALTER COLUMN id SET DEFAULT nextval('policy_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY privilege ALTER COLUMN id SET DEFAULT nextval('privilege_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY resource ALTER COLUMN id SET DEFAULT nextval('resource_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY rule ALTER COLUMN id SET DEFAULT nextval('rule_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY shadow ALTER COLUMN id SET DEFAULT nextval('shadow_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY video ALTER COLUMN id SET DEFAULT nextval('video_id_seq'::regclass);


--
-- Data for Name: api_key; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY api_key (id, groupid, accesskeyid, accesssecret, consumerkey, active) FROM stdin;
\.


--
-- Name: api_key_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('api_key_id_seq', 1, false);


--
-- Data for Name: catalog; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY catalog (id, groupid, name, path, catalogtype, renamepattern, enabled, cleaned, modified, created) FROM stdin;
\.


--
-- Name: catalog_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('catalog_id_seq', 1, false);


--
-- Data for Name: detail; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY detail (id, userid, firstname, lastname, dob, organisation, alias, addressline1, addressline2, addressline3, addressline4, postcode, country, telephone1, telephone2, image1, image2, description1, description2) FROM stdin;
\.


--
-- Name: detail_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('detail_id_seq', 1, false);


--
-- Data for Name: epg; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY epg (id, stream_uri, title, sub_title, episode, text, start, duration, service, channel) FROM stdin;
\.


--
-- Data for Name: group; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY "group" (id, owner, name, active) FROM stdin;
\.


--
-- Name: group_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('group_id_seq', 1, false);


--
-- Data for Name: group_member; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY group_member (id, groupid, userid, relatedto, accepted) FROM stdin;
\.


--
-- Name: group_member_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('group_member_id_seq', 1, false);


--
-- Data for Name: live_stream; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY live_stream (id, name, url, genre, catalogid, frequency) FROM stdin;
\.


--
-- Name: live_stream_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('live_stream_id_seq', 1, false);


--
-- Data for Name: policy; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY policy (id, groupid, name, namespacekey, comment) FROM stdin;
\.


--
-- Name: policy_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('policy_id_seq', 1, false);


--
-- Data for Name: privilege; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY privilege (id, name) FROM stdin;
\.


--
-- Name: privilege_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('privilege_id_seq', 1, false);


--
-- Data for Name: resource; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY resource (id, parentid, name, description) FROM stdin;
\.


--
-- Name: resource_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('resource_id_seq', 1, false);


--
-- Data for Name: rule; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY rule (id, groupid, resourceid, privilegeid, allow) FROM stdin;
\.


--
-- Name: rule_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('rule_id_seq', 1, false);


--
-- Data for Name: shadow; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY shadow (id, username, password, salt, email, active, admin, organisation, service, banned, restkey, resetexpires) FROM stdin;
\.


--
-- Name: shadow_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('shadow_id_seq', 1, false);


--
-- Data for Name: video; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY video (id, catalogid, name, size, hash, bitrate, mode, "time", modified, created) FROM stdin;
\.


--
-- Name: video_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('video_id_seq', 1, false);


--
-- Name: api_key_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY api_key
    ADD CONSTRAINT api_key_pkey PRIMARY KEY (id);


--
-- Name: catalog_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY catalog
    ADD CONSTRAINT catalog_pkey PRIMARY KEY (id);


--
-- Name: detail_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY detail
    ADD CONSTRAINT detail_pkey PRIMARY KEY (id);


--
-- Name: detail_shadowid_unq; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY detail
    ADD CONSTRAINT detail_shadowid_unq UNIQUE (userid);


--
-- Name: epg_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY epg
    ADD CONSTRAINT epg_pkey PRIMARY KEY (id);


--
-- Name: group_member_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY group_member
    ADD CONSTRAINT group_member_pkey PRIMARY KEY (id);


--
-- Name: group_member_shadowid_relatedto_unq; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY group_member
    ADD CONSTRAINT group_member_shadowid_relatedto_unq UNIQUE (userid, relatedto);


--
-- Name: group_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY "group"
    ADD CONSTRAINT group_pkey PRIMARY KEY (id);


--
-- Name: live_stream_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY live_stream
    ADD CONSTRAINT live_stream_pkey PRIMARY KEY (id);


--
-- Name: policy_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY policy
    ADD CONSTRAINT policy_pkey PRIMARY KEY (id);


--
-- Name: privilege_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY privilege
    ADD CONSTRAINT privilege_pkey PRIMARY KEY (id);


--
-- Name: resource_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY resource
    ADD CONSTRAINT resource_pkey PRIMARY KEY (id);


--
-- Name: rule_groupid_resourceid_privilegeid_unq; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY rule
    ADD CONSTRAINT rule_groupid_resourceid_privilegeid_unq UNIQUE (groupid, resourceid, privilegeid);


--
-- Name: rule_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY rule
    ADD CONSTRAINT rule_pkey PRIMARY KEY (id);


--
-- Name: shadow_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY shadow
    ADD CONSTRAINT shadow_pkey PRIMARY KEY (id);


--
-- Name: video_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY video
    ADD CONSTRAINT video_pkey PRIMARY KEY (id);


--
-- Name: api_key_groupid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY api_key
    ADD CONSTRAINT api_key_groupid_fkey FOREIGN KEY (groupid) REFERENCES "group"(id);


--
-- Name: catalog_groupid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY catalog
    ADD CONSTRAINT catalog_groupid_fkey FOREIGN KEY (groupid) REFERENCES "group"(id);


--
-- Name: detail_userid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY detail
    ADD CONSTRAINT detail_userid_fkey FOREIGN KEY (userid) REFERENCES shadow(id);


--
-- Name: group_member_groupid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY group_member
    ADD CONSTRAINT group_member_groupid_fkey FOREIGN KEY (groupid) REFERENCES "group"(id);


--
-- Name: group_member_relatedto_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY group_member
    ADD CONSTRAINT group_member_relatedto_fkey FOREIGN KEY (relatedto) REFERENCES shadow(id);


--
-- Name: group_member_userid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY group_member
    ADD CONSTRAINT group_member_userid_fkey FOREIGN KEY (userid) REFERENCES shadow(id);


--
-- Name: group_owner_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY "group"
    ADD CONSTRAINT group_owner_fkey FOREIGN KEY (owner) REFERENCES shadow(id);


--
-- Name: policy_groupid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY policy
    ADD CONSTRAINT policy_groupid_fkey FOREIGN KEY (groupid) REFERENCES "group"(id);


--
-- Name: rule_groupid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY rule
    ADD CONSTRAINT rule_groupid_fkey FOREIGN KEY (groupid) REFERENCES "group"(id);


--
-- Name: rule_privilegeid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY rule
    ADD CONSTRAINT rule_privilegeid_fkey FOREIGN KEY (privilegeid) REFERENCES privilege(id);


--
-- Name: rule_resourceid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY rule
    ADD CONSTRAINT rule_resourceid_fkey FOREIGN KEY (resourceid) REFERENCES resource(id);


--
-- Name: public; Type: ACL; Schema: -; Owner: postgres
--

REVOKE ALL ON SCHEMA public FROM PUBLIC;
REVOKE ALL ON SCHEMA public FROM postgres;
GRANT ALL ON SCHEMA public TO postgres;
GRANT ALL ON SCHEMA public TO PUBLIC;


--
-- PostgreSQL database dump complete
--

