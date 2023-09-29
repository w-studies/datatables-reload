drop schema if exists datatables;
create schema datatables;
use datatables;

create table items(
id int(9) auto_increment primary key,
item varchar(192) unique,
created_at timestamp default current_timestamp
) engine=InnoDB;

insert into items(item) values ('item 1'), ('item 2'), ('item 3');
