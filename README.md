# Redis Board

Redis를 사용하는 PHP 게시판 애플리케이션

## 기술 스택
- PHP 8.4+
- Slim Framework 4
- Redis (데이터베이스)
- Twig (템플릿 엔진)
- PHP Redis Extension (Redis 클라이언트)

## 개발 도구
- PHPStan (Level 8) - 정적 분석
- PHP CS Fixer - 코드 스타일 자동 수정
- PHP_CodeSniffer - PSR 규칙 준수 검사
- Psalm - 추가 정적 분석
- PHPUnit - 단위 테스트

## 요구사항
- PHP 8.4 이상
- Redis Server
- PHP Redis Extension
- Composer

### PHP Redis Extension 설치

#### Ubuntu/Debian
```bash
sudo apt-get install php-redis
```

#### CentOS/RHEL
```bash
sudo yum install php-redis
```

#### macOS (Homebrew)
```bash
brew install php-redis
```

#### 소스에서 컴파일
```bash
pecl install redis
```

설치 후 `php.ini`에 다음 라인을 추가하세요:
```ini
extension=redis
```

## 설치

1. 저장소 클론
```bash
git clone https://github.com/toymoa/prboard.git
cd prboard
```

2. 의존성 설치
```bash
composer install
npm install
```

3. CSS 빌드
```bash
npm run build-css-prod  # 프로덕션용 빌드
# 또는
npm run dev             # 개발용 (watch 모드)
```

4. 환경 설정
```bash
cp .env.example .env
# .env 파일에서 Redis 연결 정보 설정
```

4. Redis 서버 실행 (Docker 사용 예시)
```bash
docker run -d -p 6379:6379 redis:latest
```

5. 로컬 서버 실행
```bash
php -S localhost:8000 -t public
```

## 웹 서버 설정

### Apache (.htaccess)
`public/.htaccess` 파일이 자동으로 처리합니다.

### Nginx
```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /home/계정명/www/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php$is_args$args;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.4-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

## 개발

### 코드 품질 검사
```bash
# 모든 품질 검사 실행
composer run quality

# 개별 도구 실행
composer run phpstan      # PHPStan 정적 분석
composer run phpcs        # 코딩 표준 검사
composer run psalm        # Psalm 정적 분석
composer run test         # 단위 테스트
```

### 코드 자동 수정
```bash
composer run phpcbf           # PHP_CodeSniffer 자동 수정
composer run php-cs-fixer     # PHP CS Fixer 자동 수정
```

## 프로젝트 구조

```
├── config/                 # 설정 파일
│   ├── dependencies.php    # DI 컨테이너 설정
│   ├── middleware.php      # 미들웨어 설정
│   ├── routes.php         # 라우트 정의
│   └── settings.php       # 애플리케이션 설정
├── public/                # 웹 루트 (Apache/Nginx 문서 루트)
│   └── index.php         # 진입점
├── src/                   # 소스 코드
│   ├── Controllers/      # 컨트롤러
│   ├── Models/          # 모델
│   ├── Services/        # 서비스 레이어
│   ├── Middleware/      # 커스텀 미들웨어
│   └── Exceptions/      # 커스텀 예외
├── templates/             # Twig 템플릿 (웹 루트 외부)
│   ├── layouts/          # 레이아웃 템플릿
│   ├── posts/           # 게시글 관련 템플릿
│   └── errors/          # 에러 템플릿
├── cache/                # 캐시 디렉토리 (웹 루트 외부)
│   └── twig/            # Twig 컴파일된 템플릿 캐시
├── tests/                # 테스트 파일
├── logs/                 # 로그 파일 (웹 루트 외부)
├── vendor/               # Composer 의존성 (웹 루트 외부)
├── phpstan.neon         # PHPStan 설정
├── .php-cs-fixer.php    # PHP CS Fixer 설정
├── phpcs.xml            # PHP_CodeSniffer 설정
└── psalm.xml            # Psalm 설정
```

## API 엔드포인트

- `GET /` - 게시글 목록
- `GET /posts/{id}` - 게시글 상세
- `POST /posts` - 게시글 작성
- `PUT /posts/{id}` - 게시글 수정
- `DELETE /posts/{id}` - 게시글 삭제

## 코딩 표준

이 프로젝트는 다음 표준을 준수합니다:

- **PSR-1**: 기본 코딩 표준
- **PSR-4**: 오토로더 표준
- **PSR-12**: 확장된 코딩 스타일 가이드
- **PHPStan Level 8**: 최고 수준의 정적 분석
- **Strict Types**: 모든 PHP 파일에서 strict_types 선언

### 코드 품질 규칙

- 모든 클래스는 `final`로 선언
- 생성자 속성 프로모션 사용
- 타입 힌트 필수 (return type 포함)
- DocBlock으로 상세한 타입 정보 제공
- 예외 처리는 명시적으로 선언
