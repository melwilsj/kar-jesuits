import Colors from '@/constants/Colors';
import { useColorScheme } from '@/hooks/useSettings';

// Define the type for color names based on the keys in Colors.light (assuming light/dark have same keys)
export type ColorSchemeName = keyof typeof Colors.light;

export function useThemeColor(
  props: { light?: string; dark?: string },
  colorName: ColorSchemeName
): string {
  const theme = useColorScheme() ?? 'light';
  const colorFromProps = props?.[theme];

  if (colorFromProps) {
    return colorFromProps;
  } else {
    return Colors[theme][colorName];
  }
}
