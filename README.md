# Jesuit Community Mobile App

This is the mobile application for the Jesuit Community Management System, built with [Expo](https://expo.dev) and integrated with Firebase Authentication.

## Project Structure
mobile/
├── app/ # Main application directory (file-based routing)
│ ├── (auth)/ # Authentication stack
│ │ ├── login.tsx # Phone & Google login screen
│ │ ├── verify.tsx # Phone verification screen
│ │ └── layout.tsx # Auth layout
│ ├── (app)/ # Main app stack
│ │ ├── home/ # Home stack
│ │ │ ├── index.tsx # Home screen
│ │ │ └── profile.tsx # Profile screen
│ │ └── layout.tsx # App layout
│ └── layout.tsx # Root layout
├── components/ # Reusable components
│ ├── auth/ # Auth-related components
│ │ ├── PhoneLogin.tsx
│ │ └── GoogleLogin.tsx
│ └── ui/ # UI components
├── constants/ # App constants
│ ├── Colors.ts
│ └── Config.ts
├── hooks/ # Custom hooks
│ ├── useAuth.ts
│ └── useFirebase.ts
├── services/ # Business logic
│ └── firebase.ts
├── utils/ # Utility functions
│ └── auth.ts
└── assets/ # Static assets
├── images/
└── fonts/

## Setup Instructions

1. Install dependencies
   ```bash
   npm install
   ```

2. Configure Firebase
   Create a `.env` file in the mobile directory:
   ```env
   EXPO_PUBLIC_FIREBASE_API_KEY=your_api_key
   EXPO_PUBLIC_FIREBASE_AUTH_DOMAIN=your_auth_domain
   EXPO_PUBLIC_FIREBASE_PROJECT_ID=your_project_id
   EXPO_PUBLIC_FIREBASE_APP_ID=your_app_id
   EXPO_PUBLIC_API_URL=http://localhost:8000/api/v1
   ```

3. Start the development server
   ```bash
   npx expo start
   ```

## Available Scripts

- `npx expo start` - Start the development server
- `npx expo start --android` - Start for Android
- `npx expo start --ios` - Start for iOS
- `npx expo start --web` - Start for web

In the output, you'll find options to open the app in a

- [development build](https://docs.expo.dev/develop/development-builds/introduction/)
- [Android emulator](https://docs.expo.dev/workflow/android-studio-emulator/)
- [iOS simulator](https://docs.expo.dev/workflow/ios-simulator/)
- [Expo Go](https://expo.dev/go), a limited sandbox for trying out app development with Expo

You can start developing by editing the files inside the **app** directory. This project uses [file-based routing](https://docs.expo.dev/router/introduction).

## Get a fresh project

When you're ready, run:

```bash
npm run reset-project
```

This command will move the starter code to the **app-example** directory and create a blank **app** directory where you can start developing.

## Learn more

To learn more about developing your project with Expo, look at the following resources:

- [Expo documentation](https://docs.expo.dev/): Learn fundamentals, or go into advanced topics with our [guides](https://docs.expo.dev/guides).
- [Learn Expo tutorial](https://docs.expo.dev/tutorial/introduction/): Follow a step-by-step tutorial where you'll create a project that runs on Android, iOS, and the web.
