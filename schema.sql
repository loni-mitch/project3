DROP DATABASE IF EXISTS cheapomail;
CREATE DATABASE cheapomail;
USE cheapomail;

-- create table 'users'

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
    `id` int NOT NULL auto_increment PRIMARY KEY,
    `firstname` char(255) NOT NULL default '',
    `lastname` char(255) NOT NULL default '',
    `username` char(255) NOT NULL default '',
    `password` char(255) NOT NULL default ''
);

-- create table 'messages'

DROP TABLE IF EXISTS `messages`;
CREATE TABLE `messages` (
    `id` int NOT NULL auto_increment PRIMARY KEY,
    `recipient_id` int NOT NULL default '0',
    `user_id` int NOT NULL default '0',
    `subject` char(255) NOT NULL default '',
    `body` char(255) NOT NULL default '',
    `date_sent` DATE
);

-- create table messages_read

DROP TABLE IF EXISTS `messages_read`;
CREATE TABLE `messages_read` (
    `id` int NOT NULL auto_increment PRIMARY KEY,
    `message_id` int NOT NULL default '0',
    `reader_id` int NOT NULL default '0',
    `date_read` DATE
);

-- Create Admin Account

INSERT INTO `users` VALUES(1,'admin','admin','admin', sha1('password'));