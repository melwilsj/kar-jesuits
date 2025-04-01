import React from 'react';
import { View, StyleSheet, ViewStyle, StyleProp, ScrollView, RefreshControl } from 'react-native';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { useThemeColor } from '@/hooks/useThemeColor';

interface ScreenContainerProps {
  children: React.ReactNode;
  style?: StyleProp<ViewStyle>;
  scrollable?: boolean;
  refreshing?: boolean;
  onRefresh?: () => void;
}

const ScreenContainer: React.FC<ScreenContainerProps> = ({
  children,
  style,
  scrollable = false,
  refreshing = false,
  onRefresh,
}) => {
  const insets = useSafeAreaInsets();
  const backgroundColor = useThemeColor({}, 'background'); // Use themed background
  const tintColor = useThemeColor({}, 'primary'); // For refresh control

  const containerStyle = [
    styles.container,
    {
      backgroundColor, // Apply themed background color
      paddingTop: insets.top,
      paddingBottom: insets.bottom,
      paddingLeft: insets.left,
      paddingRight: insets.right,
    },
    style,
  ];

  if (scrollable) {
    return (
      <ScrollView
        style={containerStyle}
        contentContainerStyle={styles.scrollContentContainer}
        keyboardShouldPersistTaps="handled"
        refreshControl={
          onRefresh ? (
            <RefreshControl
              refreshing={refreshing}
              onRefresh={onRefresh}
              colors={[tintColor]} // Use themed color
              tintColor={tintColor} // Use themed color for iOS
            />
          ) : undefined
        }
      >
        {children}
      </ScrollView>
    );
  }

  return <View style={containerStyle}>{children}</View>;
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    // backgroundColor is set dynamically
  },
  scrollContentContainer: {
    flexGrow: 1, // Ensure content can grow to fill scroll view if needed
  },
});

export default ScreenContainer;
