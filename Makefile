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
 