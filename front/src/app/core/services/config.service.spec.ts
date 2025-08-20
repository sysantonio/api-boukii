import { ConfigService } from './config.service';

describe('ConfigService runtime override', () => {
  it('uses runtime config over environment defaults', () => {
    const service = new ConfigService();

    // Baseline from environment configuration
    expect(service.getApiBaseUrl()).toBe('http://api-boukii.test/api/v5');

    // Mock runtime-config.json with a different API URL
    service.setRuntimeConfig(
      {
        api: { baseUrl: 'http://runtime.example', version: 'v9' },
      } as any
    );

    // The runtime configuration should take precedence
    expect(service.getApiBaseUrl()).toBe('http://runtime.example/api/v9');
  });
});
