# Levanta la arquitectura

file_selected := -f infrastructure/docker-compose.$(env).yml
environment := $(env)

up:
	@docker-compose $(file_selected) up

ps:
	@docker-compose $(file_selected) ps

down:
	@docker-compose $(file_selected) down

build_all:
	@docker-compose $(file_selected) build

build:
	@docker-compose $(file_selected) build $(c)

restart:
	@docker-compose $(file_selected) restart $(c)

logs:
	@docker-compose $(file_selected) logs -f $(c)

logs_php:
	@docker-compose $(file_selected) exec -T php tail -f var/logs/$(environment).log

connect:
	@docker-compose $(file_selected) exec $(c) bash

connect_root:
	@docker-compose $(file_selected) exec -u root $(c) bash

install: build_all up install_dependencies install_assets cache_clear update_database create_admin_user

install_dependencies:
	@docker-compose $(file_selected) exec -T backend composer install

install_assets:
	@docker-compose $(file_selected) exec -T backend php bin/console assets:install

cache_clear: up
	@docker-compose $(file_selected) exec -T backend php bin/console cache:clear --env=dev
	@docker-compose $(file_selected) exec -T backend php bin/console cache:clear --env=prod
	@docker-compose $(file_selected) exec -T backend rm -rf var/cache/dev
	@docker-compose $(file_selected) exec -T backend rm -rf var/cache/prod
	@docker-compose $(file_selected) exec -T backend chown -R www-data:www-data var/
	@docker-compose $(file_selected) exec -T backend chown -R www-data:www-data public/
	@docker-compose $(file_selected) exec -T backend chmod 755 -R var/cache
	@docker-compose $(file_selected) exec -T backend chown -R www-data:www-data resources/
	@docker-compose $(file_selected) exec -T backend chmod 755 -R resources/

copy_env_vars:
	cd infrastructure && cp .env.dist .env
	cd backend && cp .env.dist .env

diff_database:
	@docker-compose $(file_selected) exec -T backend php bin/console doctrine:migrations:diff

update_database:
	@docker-compose $(file_selected) exec -T backend php bin/console doctrine:migrations:migrate

create_admin_user:
	@docker-compose $(file_selected) exec -T backend php bin/console app:create-admin-user --email=i92cajaa@uco.es --password=12345678

create_areas_entity:
	@docker-compose $(file_selected) exec -T backend php bin/console app:create-areas-entity

create-permissions-group:
	@docker-compose $(file_selected) exec -T backend php bin/console app:create-permissions-group

create-permissions:
	@docker-compose $(file_selected) exec -T backend php bin/console app:create-permissions

create-roles:
	@docker-compose $(file_selected) exec -T backend php bin/console app:create-roles

create-role-permissions:
	@docker-compose $(file_selected) exec -T backend php bin/console app:create-role-permissions

reset-user-permissions:
	@docker-compose $(file_selected) exec -T backend php bin/console app:reset-users-permissions

create-users-schedules:
	@docker-compose $(file_selected) exec -T backend php bin/console app:create-users-schedules


pull_code:
	git checkout develop
	git pull

deploy: down pull_code up install_dependencies cache_clear update_database