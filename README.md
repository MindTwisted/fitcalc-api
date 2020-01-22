## Fitness calculator application

### Crons

1) run cron once a day - php bin/console task:remove-users-with-not-confirmed-emails 72
2) run cron once a week - php bin/console task:remove-expired-and-soft-deleted-refresh-tokens 2160
3) run cron once an hour - php bin/console task:remove-password-recoveries 24

### Routes list

```
POST /api/products/csv_bulk_upload
body: csv file
protection: admin

POST /api/products/bulk_upload
body: array data
protection: admin

GET /api/eating?user_id={user_id}&date={date}
protection: user
notes: user can see only eating with user_id == current_user_id, admin can see all eating
```

### ToDo

1) Add fixtures for eating and eating details
