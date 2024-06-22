restart:
	docker compose down && docker compose up -d
stop:
	docker compose down
run:
	docker compose up -d
clean:
	# docker stop $$(docker ps) 
	# docker rm $$(docker ps -a)
	docker rmi $$(docker images)
prepare:
	mkdir www bitrix upload logs logs/apache
	sudo chown -R www-data:www-data ./www ./bitrix ./upload
	sudo chmod -R 755 ./www ./bitrix ./upload
