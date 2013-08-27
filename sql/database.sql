drop table if exists dashboard_schemes;
create table dashboard_schemes (
    id integer not null auto_increment primary key,
    organisation_id integer not null,
    name varchar(75) not null
) engine=innodb;

drop table if exists widget_schemes;
create table widget_schemes (
    id integer not null auto_increment primary key,
    scheme_id integer,
    widget_id integer,
    widget_config varchar(45),
    offset_x integer,
    offset_y integer,
    width integer,
    height integer
) engine=innodb;

drop table if exists dashboard_widgets;
create table dashboard_widgets (
    id integer not null auto_increment primary key,
    name varchar(75) not null,
    description varchar(180),
    thumbnail varchar(240),
    alias varchar(45),
    config varchar(75),
    min_width integer default 0,
    min_height integer default 0,
    max_width integer default 0,
    max_height integer default 0
) engine=innodb;


