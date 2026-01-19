plugins {
    id("org.springframework.boot") version "4.0.1"
    id("io.spring.dependency-management") version "1.1.7"
    java
}

group = "com.trustledger"
version = "0.1.0"

java {
    toolchain {
        // ✅ Java 25 を使う（Boot 4.0.1 は Java 25 まで互換）
        languageVersion.set(JavaLanguageVersion.of(25))
    }
}

repositories {
    mavenCentral()
}

dependencies {
    implementation("org.springframework.boot:spring-boot-starter-web")

    // Request DTO validation
    implementation("org.springframework.boot:spring-boot-starter-validation")

    // Health check etc.
    implementation("org.springframework.boot:spring-boot-starter-actuator")

    testImplementation("org.springframework.boot:spring-boot-starter-test")
}

tasks.withType<Test> {
    useJUnitPlatform()
}