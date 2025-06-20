ALTER TABLE Users ADD COLUMN username varchar(30) 
not null unique default (left(concat(substring_index(email, '@', 1),'-',MD5(email)),30) ) 
COMMENT 'Username field that defaults to the name of the email given the hash of the email truncated to 30 chars';