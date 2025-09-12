@component('mail::message')
# 📩 Nuevo mensaje de soporte

Hola equipo de **Aura** 👋,  
se recibió una nueva solicitud de soporte:

---

**👤 Usuario:** {{ $user->nombre }}  
**📧 Correo:** {{ $user->email }}  

**📝 Asunto:** {{ $subjectLine }}

---

## Mensaje:
{{ $messageText }}

---

Gracias por usar **Aura** ✨  
@endcomponent
