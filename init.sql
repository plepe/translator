create table oauth_data (
  cookie        text            not null,
  secret        text            not null,
  token         text            not null,
  username      text            not null,
  timestamp     text            not null,
  primary key(cookie)
);
