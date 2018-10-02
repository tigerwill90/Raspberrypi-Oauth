ENV = .env

all : install

help :
	@echo ""
	@echo "Available tasks :"
	@echo ""
	@echo "  all                Install Raspberry Service"
	@echo "  install            Install Raspberry Service"
	@echo "  update             Update dependencies"
	@echo "  autoload           Update autoloader"
	@echo "  build              Build & run docker image"
	@echo ""

install : build update
	cp src/.env.example src/$(ENV)

update :
	docker-compose exec -u raspuser httpd composer update --prefer-dist
	make autoload

autoload :
	docker-compose exec -u raspuser httpd composer dump-autoload -o

build : virtual
	docker-compose down
	docker-compose build
	docker-compose up -d

virtual :
	chmod +x vhost/virtualhost.sh
	vhost/./virtualhost.sh