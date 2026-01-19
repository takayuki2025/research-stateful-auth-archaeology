# --- build stage ---
FROM gradle:8.9-jdk21 AS build
WORKDIR /app
COPY . .
RUN gradle clean bootJar --no-daemon

# --- run stage ---
FROM eclipse-temurin:21-jre
WORKDIR /app
COPY --from=build /app/build/libs/*.jar app.jar
EXPOSE 8081
ENV SERVER_PORT=8081
ENTRYPOINT ["java","-jar","/app/app.jar"]