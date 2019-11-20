fitness calculator application

-- клиент

1) неавторизованный клиент видит только титульную страницу-лендинг и форму авторизации
2) при регистрации клиент получает письмо на почту с просьбой пройти по ссылке для подтверджения регистрации
пока клиент не перейдёт по данной ссылке, его аккаунт не будет доступен для входа
после перехода по ссылке клиент сможет самостоятельно залогиниться в приложение
3) при логине в приложение будут выдаваться 2 токена, access token (jwt, 30 min) и refresh token (1 week)
при истечении срока действия access token, запрос с refresh token будет выдавать новый access token
при истечении срока действия refresh token, пользователю придется заново ввести логин и пароль
в базе будет храниться refresh token и идентификатор устройства с которого он был получен
пользователь сможет иметь несколько логинов одновременно и через профайл мониторить свои сессии, а так же иметь возможность сделать логаут определенной сессии
логаут будет происходить путём удаления refresh token из базы данных
в случае истечения срока действия refresh token, он так же будет удаляться из базы
access token хранится в базе не будет, он будет при каждом запросе проверяться с помощью ключа сохраненного на сервере
4) в случае если пользователь забыл пароль, он сможет получить ссылку для сброса пароля на свою почту
5) при входе в приложение пользователь будет сразу видеть страницу со статистикой, меню слева и навбар сверху
6) в навбаре будет кнопка профайл пользователя
в профайле пользователь сможет:
- изменять имя, логин, пароль, имейл (с подтверждением через почту)
- смотреть свои активные сессии с возможностью сделать логаут, включая текущую сессию
- изменять тему приложения (светлая/тёмная)
7) в меню слева будут следующие пункты: статистика (активная по дефолту), мой рацион, таблица калорийности
8) на странице мой рацион пользователь будет иметь возможность добавлять приемы пищи и продукты в них
количество приемов пищи и продуктов в них будет неограничено
так же пользователь будет иметь возможность смотреть, изменять и удалять приемы пищи и продукты в них за любой день
9) на странице таблица калорийности пользователь будет иметь возможность просмотреть таблицу продуктов, доступных в приложении
продукты будут в виде таблицы со столбцами (наименование, белки, жиры, углеводы, ккал)
пользователь будет иметь возможность производить фильтрацию в таблице по наименованию продукта (elasticsearch)
пользователь будет иметь возможность добавлять свои собственные продукты в данную таблицу

-- администратор

1) администратор может залогиниться в приложение с помощью логина и пароля, администратор так же будет получать access token (jwt, 30 min) и refresh token (3 hours)
2) администратор не может получить ссылку для изменения пароля на почту, пароль администратора может быть изменен только через профайл администратора в приложении, либо через базу
3) администратор может как и обычный пользователь производить манипуляции со своим профайлом
4) администратор как и обычный пользователь может видеть статистику, но по всему приложению
5) администратор так же может видеть таблицу калорийности
а так же видеть какие продукты общие и какие пользовательские
администратор может переводить пользовательские продукты в общие
6) администратор может видеть всех пользователей приложения с возможностью вносить правки в профайл пользователя
7) администратор может видеть рацион пользователей, но не может его изменять, добавлять или удалять
администратор не может вести собственный рацион

-- общее

1) приложение будет доступно в двух локализациях: русский, английский
будет таблица products и таблица products_translation
в таблице продуктов будет храниться только бжу и калорийность, в таблице переводов будет храниться наименование продукта и локаль

-- routes list

POST /api/auth/register
body: name, email, password
protection: guest

POST /api/auth/login
body: email, password
protection: guest

POST /api/auth/refresh
body: refresh_token
protection: user

POST /api/auth/logout
body: refresh_token
protection: user

POST /api/auth/reset_password
body: email
protection: guest

GET /api/auth
protection: user

GET /register_email_confirmation?hash={hash}
protection: hash

GET /reset_password?token={token}
protection: token

POST /reset_password
body: token, password, password_repeat
protection: token

GET /api/users
GET /api/users/{id}
protection: admin

PUT /api/users
body: ?name, ?password
protection: user

PUT /api/users/email
body: email
protection: user

PUT /api/users/{id}
body: ?name, ?password
protection: admin

PUT /api/users/{id}/email
body: email
protection: admin

GET /change_email_confirmation?hash={hash}
protection: hash

GET /api/products?search={name}&user_id={user_id}&offset={offset}&limit={limit}(max 100)
protection: user
notes: user can see only products with user_id == null and user_id == current_user_id, admin can see all products

POST /api/products
body: name, proteins, fats, carbohydrates, calories, ?user_id (admin only)
protection: user
notes: user can add products with user_id == current_user_id, admin can add products for all

POST /api/products/verify_upload
body: csv file
protection: admin

POST /api/products/upload
body: csv file
protection: admin

PUT /api/products/{id}
body: name, proteins, fats, carbohydrates, calories, ?user_id (admin only)
protection: user
notes: user can update only products with user_id == current_user_id, admin can update all products

DELETE /api/products/{id}
protection: user
notes: user can delete only products with user_id == current_user_id, admin can delete all products

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
