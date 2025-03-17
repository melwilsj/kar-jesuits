import { Dimensions, Platform } from 'react-native';

const { width, height } = Dimensions.get('window');

export default {
  window: {
    width,
    height,
  },
  isSmallDevice: width < 375,
  tabBarHeight: 60, // Adjust based on your TabBar height
  bottomSpacing: Platform.OS === 'ios' ? 85 : 70, // Combined TabBar + extra space
};
