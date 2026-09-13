# lora-medical-tourism_system —   نظام إدارة عيادات السياحة العلاجيةالطبية 

نظام  متكامل (باك إند + فرونت إند) لإدارة المرضى، الفواتير، التقارير الطبية، والتواصل الآلي عبر بريد إلكتروني وواتساب حقيقيين.
   هناك واجهة مخصص للمرضى بحيث تعرض لهم تقاريرهم وفواتيرهم ولوحة تحكم خاصة بكل مريض واضافة عل ذلك يمكن للمريض رفع القارير عل شكل  ملفات .

> ⚠️ **هذه نسخة عرض تجريبية  ببيانات وهمية بالكامل** — لا تحتوي أي معلومات حقيقية لمرضى أو عيادات فعلية. بُنيت لعرض المهارات التقنية فقط.

## 🔗 تجربة حية

- **الموقع**: [رابط النطاق هنا]
- **حساب تجريبي (لوحة الإدارة)**: `admin@loraclinic.com` / `admin1234`
- **حساب تجريبي (بوابة المريض)**: `fatima.rashid@email.com` / `patient123`
- ايا حساب موجود في patient Seeder يمكن تجربته وكلمة السر موحدة للجميع patient123 

## ✨ أبرز الميزات

- **نظام دعوة لتفعيل الحساب** (بنمط Slack/Google Workspace) — لا يرى الأدمن كلمة مرور المريض إطلاقًا؛ رابط تفعيل آمن يُرسَل تلقائيًا بالبريد والواتساب
- **إشعارات حقيقية فعليًا**، لا مجرد سجلات في قاعدة البيانات: بريد إلكتروني (Laravel Mail) + واتساب (WhatsApp Cloud API الرسمية من Meta)
- **استيراد مرضى بالجملة من Excel**، مع معالجة كل صف بشكل مستقل ومطابقة أعمدة عربي/إنجليزي متسامحة
- **حذف ناعم (Soft Deletes)** على السجلات الطبية والمالية — لا حذف نهائي عرضي
- **حماية Rate Limiting** حقيقية على تسجيل الدخول (بنمط مشابه لـ Laravel Fortify)
- **إدارة كاملة**: مرضى، فواتير (بحساب تلقائي للمتبقي ونسبة السداد)، تقارير طبية (رفع/عرض ملفات)، وتنبيهات مُصنَّفة

## 🛠️ التقنيات

**الباك إند**: Laravel 12 · PHP 8.2 · MySQL · Sanctum (مصادقة API بالتوكن) · PhpSpreadsheet

**الفرونت إند**: React · Vite · Tailwind CSS · JavaScript (JSX)

## 🏗️ قرارات معمارية أستحق الإشارة لها

- **API موحّد وثابت الشكل**: كل نقاط النهاية المُرقَّمة صفحيًا (Pagination) ترجع نفس البنية `{data, meta}` بلا استثناء، عبر الأدمن والمريض معًا
- **مصادقة بلا كوكيز**: توكنات Bearer فقط عبر Sanctum، مع middleware مخصص يميّز نوع الحساب (أدمن/مريض) رغم مشاركتهما جدول توكنات واحد
- **كل استيراد/رفع ملف يُعالَج دفاعيًا**: تحقق من النوع والحجم، ومعالجة كل صف/سجل بشكل مستقل بحيث لا يوقف خطأ واحد بقية العملية

## هيكل المشروع

```
app/
  Enums/                InterestLevel, InvoiceStatus, NotificationType, AdminRole
  Models/               Admin, Patient, Invoice, MedicalReport, Notification
  Services/             WhatsAppService (نداءات HTTP حقيقية لواجهة Meta Cloud API)
  Notifications/
    Channels/           WhatsAppChannel (قناة إشعار مخصصة)
    PatientAccountCreated, NewMedicalReportUploaded,
    PaymentOverdueReminder, UpcomingOperationReminder
  Http/
    Controllers/Api/
      Auth/             AdminAuthController, PatientAuthController (+ Rate Limiting)
      Admin/            Dashboard, Patient, Invoice, MedicalReport, Notification
      Patient/          Dashboard, Invoice, MedicalReport
    Requests/           تحقق من صحة كل نموذج (Store/Update)
    Resources/          تنسيق استجابات JSON
    Middleware/         EnsureUserIsAdmin, EnsureUserIsPatient
  Console/Commands/     CheckOverdueInvoices, CheckUpcomingOperations
  Observers/            MedicalReportObserver (تنبيه + إشعار حقيقي تلقائيًا)
database/
  migrations/           6 جداول (+ softDeletes على 3 منها)
  seeders/               بيانات تجريبية مطابقة للقطات الشاشة (12 مريض، 7 فواتير، 6 تقارير، 6 تنبيهات)
config/
  whatsapp.php          إعدادات Meta WhatsApp Cloud API
  frontend.php          رابط تطبيق الواجهة (للروابط داخل رسائل البريد/واتساب)
  sanctum.php           إعدادات توكنات API
routes/api.php          كل نقاط النهاية أعلاه
```


## ⚙️ التشغيل محليًا

راجع `backend/README.md` للتفاصيل الكاملة. باختصار:

```bash
composer install && npm install   # لكل من backend/ و frontend/
php artisan migrate --seed        # يزرع نفس بيانات العرض التجريبي أعلاه
php artisan serve
npm run dev
```


