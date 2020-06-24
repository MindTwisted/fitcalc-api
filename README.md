## Fitness calculator application

### Cron
1) run cron once a day - php bin/console task:remove-users-with-not-confirmed-emails 72
2) run cron once a week - php bin/console task:remove-expired-and-soft-deleted-refresh-tokens 2160
3) run cron once an hour - php bin/console task:remove-password-recoveries 24

### TODO
* POST api/eating_scheme/{id}/default
* POST api/eating_scheme/{id}/apply
