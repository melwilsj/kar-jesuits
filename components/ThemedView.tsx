import { View } from 'react-native';
import { useThemeColor } from '../hooks/useThemeColor';

export type ThemedViewProps = {
  lightColor?: string;
  darkColor?: string;
  style?: any;
  children?: React.ReactNode;
};

export function ThemedView(props: ThemedViewProps) {
  const { style, lightColor, darkColor, ...otherProps } = props;
  const backgroundColor = useThemeColor({ light: lightColor, dark: darkColor }, 'background');

  return <View style={[{ backgroundColor }, style]} {...otherProps} />;
}
