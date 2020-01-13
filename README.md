## Fitness calculator application

### Crons

1) run cron once a day - php bin/console task:remove-users-with-not-confirmed-emails 72
2) run cron once a week - php bin/console task:remove-expired-and-soft-deleted-refresh-tokens 2160
3) remove password recoveries older than 24 hours (once an hour)

### Routes list

```
POST /api/products/csv_bulk_upload
body: csv file
protection: admin

POST /api/products/bulk_upload
body: array data
protection: admin

GET /api/eating?user_id={user_id}&date={date}&offset={offset}&limit={limit}(max 100)
protection: user
notes: user can see only eating with user_id == current_user_id, admin can see all eating

POST /api/eating
body: name, occurred_at
protection: user
notes: admin can't access this route

PUT /api/eating/{id}
body: name, occurred_at
protection: user
notes: user can update only eating with user_id == current_user_id, admin can't access this route

DELETE /api/eating/{id}
protection: user
notes: user can delete only eating with user_id == current_user_id, admin can't access this route

POST /api/eating/{id}/details
body: product_id, weight
protection: user
notes: admin can't access this route

PUT /api/eating/{id}/details
body: product_id, weight
protection: user
notes: user can update only eating details of eating with user_id == current_user_id, admin can't access this route

DELETE /api/eating/{id}/details
protection: user
notes: user can delete only eating details of eating with user_id == current_user_id, admin can't access this route
```
