# CoachTech Attendance Management

## 概要
本アプリケーションは勤怠管理を行うWebアプリケーションです。
一般ユーザーは打刻・勤怠修正申請ができ、管理者は申請の承認・スタッフ管理を行います。

---

## 機能一覧

### 一般ユーザー
- 会員登録
- ログイン / ログアウト
- 出勤・退勤・休憩打刻
- 勤怠一覧表示
- 勤怠詳細表示
- 修正申請機能

### 管理者
- ログイン
- 勤怠一覧確認
- 勤怠詳細確認
- 修正申請承認
- スタッフ一覧確認
- CSV出力

---

## 使用技術

- Laravel
- PHP
- MySQL
- Docker
- Git / GitHub

---

## 環境構築手順（予定）

```bash
git clone https://github.com/riichikawashima-cmd/coachtech-attendance.git
cd coachtech-attendance
docker-compose up -d --build