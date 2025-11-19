# === pom.xml ===
<project xmlns="http://maven.apache.org/POM/4.0.0"
         xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:schemaLocation="http://maven.apache.org/POM/4.0.0
         http://maven.apache.org/xsd/maven-4.0.0.xsd">
  <modelVersion>4.0.0</modelVersion>
  <groupId>com.example</groupId>
  <artifactId>dynamic-site</artifactId>
  <version>1.0.0</version>
  <name>dynamic-site</name>
  <properties>
    <java.version>17</java.version>
    <spring-boot.version>3.3.3</spring-boot.version>
  </properties>
  <dependencies>
    <dependency>
      <groupId>org.springframework.boot</groupId>
      <artifactId>spring-boot-starter-web</artifactId>
      <version>${spring-boot.version}</version>
    </dependency>
    <dependency>
      <groupId>org.springframework.boot</groupId>
      <artifactId>spring-boot-starter-data-jpa</artifactId>
      <version>${spring-boot.version}</version>
    </dependency>
    <dependency>
      <groupId>org.postgresql</groupId>
      <artifactId>postgresql</artifactId>
      <version>42.7.4</version>
    </dependency>
  </dependencies>
  <build>
    <plugins>
      <plugin>
        <groupId>org.springframework.boot</groupId>
        <artifactId>spring-boot-maven-plugin</artifactId>
        <version>${spring-boot.version}</version>
        <configuration>
          <mainClass>com.example.site.Application</mainClass>
        </configuration>
      </plugin>
    </plugins>
    <finalName>app</finalName>
  </build>
</project>

# === src/main/java/com/example/site/Application.java ===
package com.example.site;

import org.springframework.boot.SpringApplication;
import org.springframework.boot.autoconfigure.SpringBootApplication;

@SpringBootApplication
public class Application {
  public static void main(String[] args) {
    SpringApplication.run(Application.class, args);
  }
}

# === src/main/java/com/example/site/model/User.java ===
package com.example.site.model;

import jakarta.persistence.*;

@Entity
@Table(name = "users")
public class User {
  @Id
  @GeneratedValue(strategy = GenerationType.IDENTITY)
  private Long id;

  @Column(unique = true, nullable = false, length = 50)
  private String username;

  @Column(unique = true, nullable = false, length = 100)
  private String email;

  @Column(nullable = false, length = 255)
  private String password;

  @Column(length = 140)
  private String bio;

  public User() {}

  public User(String username, String email, String password, String bio) {
    this.username = username;
    this.email = email;
    this.password = password;
    this.bio = bio;
  }

  public Long getId() { return id; }
  public String getUsername() { return username; }
  public void setUsername(String username) { this.username = username; }
  public String getEmail() { return email; }
  public void setEmail(String email) { this.email = email; }
  public String getPassword() { return password; }
  public void setPassword(String password) { this.password = password; }
  public String getBio() { return bio; }
  public void setBio(String bio) { this.bio = bio; }
}

# === src/main/java/com/example/site/repo/UserRepository.java ===
package com.example.site.repo;

import com.example.site.model.User;
import org.springframework.data.jpa.repository.JpaRepository;

import java.util.Optional;

public interface UserRepository extends JpaRepository<User, Long> {
  Optional<User> findByUsername(String username);
}

# === src/main/java/com/example/site/controller/UserController.java ===
package com.example.site.controller;

import com.example.site.model.User;
import com.example.site.repo.UserRepository;
import org.springframework.web.bind.annotation.*;

import java.util.List;

@RestController
@RequestMapping("/api/users")
@CrossOrigin(origins = "*")
public class UserController {
  private final UserRepository repo;

  public UserController(UserRepository repo) {
    this.repo = repo;
  }

  @GetMapping
  public List<User> all() {
    return repo.findAll();
  }

  @PostMapping
  public User create(@RequestBody User user) {
    return repo.save(user);
  }

  @GetMapping("/{id}")
  public User one(@PathVariable Long id) {
    return repo.findById(id).orElse(null);
  }

  @PutMapping("/{id}")
  public User update(@PathVariable Long id, @RequestBody User updated) {
    return repo.findById(id).map(u -> {
      u.setUsername(updated.getUsername());
      u.setEmail(updated.getEmail());
      u.setPassword(updated.getPassword());
      u.setBio(updated.getBio());
      return repo.save(u);
    }).orElse(null);
  }

  @DeleteMapping("/{id}")
  public void delete(@PathVariable Long id) {
    repo.deleteById(id);
  }
}

# === src/main/resources/application.properties ===
# Read DB settings from environment variables (no code edits needed)
spring.datasource.url=${DB_URL}
spring.datasource.username=${DB_USERNAME}
spring.datasource.password=${DB_PASSWORD}
spring.jpa.hibernate.ddl-auto=update
spring.jpa.show-sql=false
spring.sql.init.mode=always

# Server port (Beanstalk will map it)
server.port=5000

# === src/main/resources/data.sql ===
-- Seed sample users (auto-loaded)
INSERT INTO users (username, email, password, bio)
VALUES
  ('ayesha', 'ayesha@example.com', 'secret123', 'Designer from Karachi'),
  ('hamza', 'hamza@example.com', 'secret123', 'Full-stack dev'),
  ('fatima', 'fatima@example.com', 'secret123', 'Photographer & blogger');

# === src/main/resources/static/index.html ===
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>User Profiles</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    body { font-family: system-ui, Arial; margin: 2rem; background:#f6f8fb; }
    h1 { margin-bottom: .5rem; }
    .card { background:#fff; padding:1rem; border-radius:12px; box-shadow:0 4px 12px rgba(0,0,0,.08); margin-bottom:1rem; }
    label { display:block; margin:.5rem 0 .25rem; }
    input, textarea { width:100%; padding:.6rem; border:1px solid #cbd5e1; border-radius:8px; }
    button { background:#2563eb; color:#fff; border:none; padding:.6rem 1rem; border-radius:8px; cursor:pointer; }
    button.secondary { background:#0ea5e9; }
    .grid { display:grid; grid-template-columns: repeat(auto-fill, minmax(280px,1fr)); gap:1rem; }
    .row { display:flex; gap:.5rem; }
  </style>
</head>
<body>
  <h1>User profiles</h1>
  <p>View, add, and edit user profiles. Backend: Spring Boot + PostgreSQL.</p>

  <div class="card">
    <h3>Add / update user</h3>
    <div id="form">
      <label>Username</label>
      <input id="username" placeholder="e.g., ayesha">
      <label>Email</label>
      <input id="email" placeholder="e.g., ayesha@example.com">
      <label>Password</label>
      <input id="password" type="password" placeholder="••••••">
      <label>Bio</label>
      <textarea id="bio" rows="3" placeholder="Short bio"></textarea>
      <div class="row" style="margin-top:.75rem;">
        <button onclick="createUser()">Save</button>
        <button class="secondary" onclick="loadUsers()">Refresh</button>
      </div>
    </div>
  </div>

  <div class="grid" id="users"></div>

<script>
const API = '/api/users';

async function loadUsers() {
  const res = await fetch(API);
  const users = await res.json();
  const container = document.getElementById('users');
  container.innerHTML = '';
  users.forEach(u => {
    const card = document.createElement('div');
    card.className = 'card';
    card.innerHTML = `
      <h3>${u.username}</h3>
      <p><b>Email:</b> ${u.email}</p>
      <p>${u.bio || ''}</p>
      <div class="row">
        <button onclick="prefill(${u.id}, '${escapeHTML(u.username)}', '${escapeHTML(u.email)}', '${escapeHTML(u.bio||'')}', '${escapeHTML(u.password)}')">Edit</button>
        <button class="secondary" onclick="removeUser(${u.id})">Delete</button>
      </div>
    `;
    container.appendChild(card);
  });
}

function escapeHTML(str) {
  return String(str).replace(/[&<>"']/g, s => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[s]));
}

function prefill(id, username, email, bio, password) {
  document.getElementById('username').value = username;
  document.getElementById('email').value = email;
  document.getElementById('bio').value = bio;
  document.getElementById('password').value = password;
  // Update on save
  window.updateId = id;
}

async function createUser() {
  const payload = {
    username: document.getElementById('username').value.trim(),
    email: document.getElementById('email').value.trim(),
    password: document.getElementById('password').value,
    bio: document.getElementById('bio').value.trim()
  };
  if (window.updateId) {
    await fetch(`${API}/${window.updateId}`, {
      method: 'PUT',
      headers: { 'Content-Type':'application/json' },
      body: JSON.stringify(payload)
    });
    window.updateId = null;
  } else {
    await fetch(API, {
      method: 'POST',
      headers: { 'Content-Type':'application/json' },
      body: JSON.stringify(payload)
    });
  }
  document.getElementById('username').value = '';
  document.getElementById('email').value = '';
  document.getElementById('password').value = '';
  document.getElementById('bio').value = '';
  loadUsers();
}

async function removeUser(id) {
  await fetch(`${API}/${id}`, { method: 'DELETE' });
  loadUsers();
}

loadUsers();
</script>
</body>
</html>

# === .github/workflows/deploy.yml ===
name: CI/CD Pipeline to AWS

on:
  push:
    branches:
      - main

jobs:
  build-deploy:
    runs-on: ubuntu-latest
    steps:
      - name: Checkout Code
        uses: actions/checkout@v3

      - name: Set up JDK
        uses: actions/setup-java@v4
        with:
          java-version: '17'
          distribution: 'temurin'

      - name: Build with Maven
        run: mvn -q -DskipTests clean package

      - name: Deploy to Elastic Beanstalk
        uses: einaregilsson/beanstalk-deploy@v21
        with:
          aws_access_key: ${{ secrets.AWS_ACCESS_KEY_ID }}
          aws_secret_key: ${{ secrets.AWS_SECRET_ACCESS_KEY }}
          application_name: "my-app"
          environment_name: "my-env"
          version_label: ${{ github.sha }}
          region: "us-east-1"
          file: "target/app.jar"
