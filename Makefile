down:
	@docker compose down -v
.PHONY: down

deps:
	@docker compose --profile deps run --rm composer install
.PHONY: deps

cli:
	@docker compose --profile cli run --rm zod bash
.PHONY: cli

http:
	@docker compose --profile http up --build -d
.PHONY: http