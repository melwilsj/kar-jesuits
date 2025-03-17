import React from 'react';
import { View, StyleSheet, SafeAreaView } from 'react-native';
import Layout from '@/constants/Layout';
import Colors from '@/constants/Colors';
import { useColorScheme } from 'react-native';

type ScreenContainerProps = {
  children: React.ReactNode;
  style?: object;
  withScrollView?: boolean;
};

export default function ScreenContainer({ 
  children, 
  style, 
  withScrollView = false 
}: ScreenContainerProps) {
  const colorScheme = useColorScheme();
  const isDark = colorScheme === 'dark';
  
  return (
    <SafeAreaView 
      style={[
        styles.container, 
        { backgroundColor: isDark ? Colors.gray[100] : Colors.background },
        style
      ]}
    >
      {children}
      {/* Add spacer at the bottom */}
      <View style={styles.bottomSpacer} />
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
  },
  bottomSpacer: {
    height: Layout.bottomSpacing-20,
  }
});
