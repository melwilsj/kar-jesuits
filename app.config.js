export default {
  expo: {
    name: "Kar-Jesuits",
    slug: "kar-jesuits",
    version: "0.0.1",
    orientation: "portrait",
    icon: "./assets/images/icon.png",
    scheme: "myapp",
    userInterfaceStyle: "automatic",
    newArchEnabled: true,
    ios: {
      supportsTablet: true,
      bundleIdentifier: "com.ksjdevs.karjesuits"
    },
    android: {
      googleServicesFile: process.env.GOOGLE_SERVICES_JSON,
      adaptiveIcon: {
        foregroundImage: "./assets/images/adaptive-icon.png",
        backgroundColor: "#ffffff"
      },
      package: "com.ksjdevs.karjesuits"
    },
    web: {
      bundler: "metro",
      output: "static",
      favicon: "./assets/images/favicon.png"
    },
    plugins: [
      "expo-router",
      [
        "expo-splash-screen",
        {
          "image": "./assets/images/splash-icon.png",
          "backgroundColor": "#ffffff"
        }
      ],
      "@react-native-firebase/app",
      "@react-native-firebase/auth",
      "@react-native-firebase/messaging",
      [
        "expo-notifications",
        {
          "icon": "./assets/images/icon.png"
        }
      ],
      "expo-build-properties",
    ],
    experiments: {
      typedRoutes: true
    },
    extra: {
      eas: {
        projectId: "b820f31e-3130-47fe-bc82-0d7e3d19668b"
      }
    },
    owner: "melwilsj"
  }
}; 